<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\FaceApiException;
use App\Http\Controllers\Controller;
use App\Models\FaceIdentity;
use App\Services\FaceIdentityManager;
use App\Services\FaceRecognitionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FaceAuthController extends Controller
{
    /**
     * Identify the physical person first. Account/role selection happens only
     * after the identity has been verified.
     */
    public function login(
        Request $request,
        FaceRecognitionService $faceService,
        FaceIdentityManager $identityManager
    ): JsonResponse {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        if (! $this->identityTablesReady()) {
            return response()->json([
                'success' => false,
                'message' => 'Face Identity belum siap. Jalankan migration dan konsolidasi template wajah.',
            ], 503);
        }

        $cooldownMs = max(
            2000,
            min(10000, (int) config('services.face_api.login_cooldown_ms', 3000))
        );
        $clientKey = $this->clientKey($request);
        $cooldownKey = $clientKey.':cooldown';
        $lockKey = $clientKey.':processing';
        $nowMs = (int) floor(microtime(true) * 1000);
        $cooldownUntilMs = (int) Cache::get($cooldownKey, 0);

        if ($cooldownUntilMs > $nowMs) {
            return $this->tooManyRequests($cooldownUntilMs - $nowMs);
        }

        $lock = Cache::lock($lockKey, 75);
        if (! $lock->get()) {
            return $this->tooManyRequests($cooldownMs);
        }

        try {
            return $this->attemptIdentityLogin($request, $faceService, $identityManager);
        } finally {
            $untilMs = (int) floor(microtime(true) * 1000) + $cooldownMs;
            Cache::put(
                $cooldownKey,
                $untilMs,
                now()->addSeconds((int) ceil($cooldownMs / 1000) + 1)
            );
            $lock->release();
        }
    }

    /**
     * Complete login after a verified face belongs to more than one account.
     */
    public function selectAccount(Request $request, FaceIdentityManager $identityManager): JsonResponse
    {
        $validated = $request->validate([
            'verification_token' => 'required|string|min:32|max:255',
            'guard' => 'required|string|in:user,employee,supervisor,finance',
            'account_id' => 'required|integer|min:1',
        ]);

        $cacheKey = $this->selectionCacheKey($validated['verification_token']);
        $payload = Cache::get($cacheKey);

        if (! is_array($payload)) {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi wajah sudah kedaluwarsa. Silakan lakukan pemindaian wajah kembali.',
            ], 401);
        }

        $requestedKey = $validated['guard'].':'.(int) $validated['account_id'];
        $allowedAccounts = is_array($payload['accounts'] ?? null) ? $payload['accounts'] : [];

        if (! in_array($requestedKey, $allowedAccounts, true)) {
            Log::warning('Rejected face account selection outside verified identity', [
                'requested' => $requestedKey,
                'identity_id' => $payload['identity_id'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Akun yang dipilih tidak termasuk dalam identitas wajah yang telah diverifikasi.',
            ], 403);
        }

        $identity = FaceIdentity::query()
            ->whereKey((int) ($payload['identity_id'] ?? 0))
            ->where('is_active', true)
            ->first();

        if (! $identity) {
            Cache::forget($cacheKey);
            return $this->invalidFaceLogin();
        }

        $account = $identityManager->account($validated['guard'], (int) $validated['account_id']);
        if (! $account || ! $identityManager->isAccountActive($validated['guard'], $account)) {
            Cache::forget($cacheKey);
            return $this->invalidFaceLogin();
        }

        // One successful account choice consumes the temporary verification.
        Cache::forget($cacheKey);

        $context = $identityManager->context($account);
        if (! $context) {
            return $this->invalidFaceLogin();
        }

        return $this->issueLogin(
            $account,
            $context,
            $identity,
            $payload['face'] ?? []
        );
    }

    private function attemptIdentityLogin(
        Request $request,
        FaceRecognitionService $faceService,
        FaceIdentityManager $identityManager
    ): JsonResponse {
        $identities = FaceIdentity::query()
            ->where('is_active', true)
            ->whereHas('accounts')
            ->with('accounts')
            ->get();

        if ($identities->isEmpty()) {
            return $this->invalidFaceLogin();
        }

        $references = $identities
            ->map(fn (FaceIdentity $identity) => [
                'id' => 'identity:'.$identity->getKey(),
                'embedding' => $identity->embedding,
            ])
            ->values()
            ->all();

        try {
            $result = $faceService->identify($request->file('image'), $references);
        } catch (FaceApiException $exception) {
            Log::error('Face identity service failed', [
                'api_status' => $exception->apiStatus ?? null,
                'reference_count' => count($references),
                'error' => $exception->getMessage(),
            ]);

            if ($exception->isClientError()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wajah tidak dapat diproses. Pastikan hanya satu wajah terlihat jelas dan cukup dekat dengan kamera.',
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Layanan pengenalan wajah sedang tidak tersedia.',
            ], 503);
        }

        if (! (bool) ($result['identified'] ?? false)) {
            return $this->invalidFaceLogin();
        }

        $matchId = (string) ($result['match_id'] ?? '');
        if (! preg_match('/^identity:(\d+)$/', $matchId, $matches)) {
            return $this->invalidFaceLogin();
        }

        $identity = $identities->firstWhere('id', (int) $matches[1]);
        if (! $identity) {
            return $this->invalidFaceLogin();
        }

        $options = $identityManager->accountOptions($identity);
        if (count($options) === 0) {
            return $this->invalidFaceLogin();
        }

        $faceMeta = [
            'similarity' => $result['similarity'] ?? null,
            'threshold' => $result['threshold'] ?? null,
        ];

        // If this physical person only has one active account, preserve the
        // fastest camera-only experience and log in immediately.
        if (count($options) === 1) {
            $option = $options[0];

            return $this->issueLogin(
                $option['account'],
                [
                    'guard' => $option['guard'],
                    'label' => $option['role_label'],
                    'token_name' => $option['token_name'],
                ],
                $identity,
                $faceMeta
            );
        }

        $verificationToken = Str::random(80);
        $ttlSeconds = max(
            30,
            min(180, (int) config('services.face_api.selection_ttl_seconds', 60))
        );

        Cache::put(
            $this->selectionCacheKey($verificationToken),
            [
                'identity_id' => $identity->getKey(),
                'accounts' => array_map(
                    static fn (array $option) => $option['guard'].':'.$option['account_id'],
                    $options
                ),
                'face' => $faceMeta,
            ],
            now()->addSeconds($ttlSeconds)
        );

        return response()->json([
            'success' => true,
            'requires_account_selection' => true,
            'message' => 'Wajah dikenali. Pilih akun yang ingin digunakan.',
            'verification_token' => $verificationToken,
            'expires_in' => $ttlSeconds,
            'accounts' => array_map(
                static fn (array $option) => [
                    'guard' => $option['guard'],
                    'account_id' => $option['account_id'],
                    'name' => $option['name'],
                    'role_label' => $option['role_label'],
                ],
                $options
            ),
            'face' => $faceMeta,
        ]);
    }

    private function issueLogin(
        Model $account,
        array $context,
        FaceIdentity $identity,
        array $faceMeta = []
    ): JsonResponse {
        $account->tokens()->delete();
        $token = $account->createToken($context['token_name'])->plainTextToken;
        $identity->forceFill(['last_verified_at' => now()])->save();

        return response()->json([
            'success' => true,
            'requires_account_selection' => false,
            'message' => 'Wajah dikenali. Login berhasil.',
            'token' => $token,
            'user' => [
                'id' => $account->getKey(),
                'name' => $account->name,
                'email' => $account->email,
                'guard' => $context['guard'],
                'login_method' => 'face',
            ],
            'face' => [
                'similarity' => $faceMeta['similarity'] ?? null,
                'threshold' => $faceMeta['threshold'] ?? null,
            ],
        ]);
    }

    private function clientKey(Request $request): string
    {
        $fingerprint = implode('|', [
            (string) $request->ip(),
            substr((string) $request->userAgent(), 0, 180),
        ]);

        return 'face-login:'.hash('sha256', $fingerprint);
    }

    private function selectionCacheKey(string $token): string
    {
        return 'face-login-selection:'.hash('sha256', $token);
    }

    private function tooManyRequests(int $retryAfterMs): JsonResponse
    {
        $retryAfterMs = max(500, $retryAfterMs);
        $retryAfterSeconds = max(1, (int) ceil($retryAfterMs / 1000));

        return response()
            ->json([
                'success' => false,
                'message' => 'Verifikasi wajah sedang dalam jeda. Silakan tetap lihat ke kamera.',
                'retry_after_ms' => $retryAfterMs,
            ], 429)
            ->header('Retry-After', (string) $retryAfterSeconds);
    }

    private function invalidFaceLogin(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Wajah tidak dikenali atau belum terdaftar.',
        ], 401);
    }

    private function identityTablesReady(): bool
    {
        return Schema::hasTable('face_identities') && Schema::hasTable('face_identity_accounts');
    }
}
