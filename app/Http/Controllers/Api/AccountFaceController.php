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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AccountFaceController extends Controller
{
    public function status(Request $request, FaceIdentityManager $identityManager): JsonResponse
    {
        $account = $request->user();
        $context = $identityManager->context($account);

        if (! $account || ! $context) {
            return $this->unsupportedAccount();
        }

        if (! $this->identityTablesReady()) {
            return response()->json([
                'success' => true,
                'data' => $this->statusPayload(null, true),
                'message' => 'Face Identity belum siap. Jalankan migration database terlebih dahulu.',
            ]);
        }

        $identity = $identityManager->identityForAccount($account);

        return response()->json([
            'success' => true,
            'data' => $this->statusPayload($identity),
        ]);
    }

    public function enroll(
        Request $request,
        FaceRecognitionService $faceService,
        FaceIdentityManager $identityManager
    ): JsonResponse {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $account = $request->user();
        $context = $identityManager->context($account);

        if (! $account || ! $context) {
            return $this->unsupportedAccount();
        }

        if (! $this->identityTablesReady()) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel Face Identity belum tersedia. Jalankan php artisan migrate.',
            ], 503);
        }

        try {
            $face = $faceService->createEmbedding($request->file('image'));
        } catch (FaceApiException $exception) {
            Log::warning('Face identity enrollment API failed', [
                'guard' => $context['guard'],
                'account_id' => $account->getKey(),
                'face_api_status' => $exception->apiStatus,
                'error' => $exception->getMessage(),
            ]);

            $status = $exception->isClientError() ? 422 : 503;
            $message = $status === 422
                ? 'Wajah tidak dapat diproses. Pastikan hanya satu wajah terlihat jelas.'
                : 'Layanan verifikasi wajah sedang tidak tersedia.';

            if (config('app.debug')) {
                $message .= ' Detail: '.$exception->getMessage();
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'face_api_status' => config('app.debug') ? $exception->apiStatus : null,
            ], $status);
        }

        try {
            $result = $identityManager->enroll($account, $face);
        } catch (\Throwable $exception) {
            Log::error('Failed to save face identity enrollment', [
                'guard' => $context['guard'],
                'account_id' => $account->getKey(),
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug')
                    ? 'Gagal menyimpan Face Identity. Detail: '.$exception->getMessage()
                    : 'Gagal menyimpan Face Identity.',
            ], 500);
        }

        /** @var FaceIdentity $identity */
        $identity = $result['identity'];
        $shared = (int) ($result['account_count'] ?? 1) > 1;

        return response()->json([
            'success' => true,
            'message' => $shared
                ? 'Wajah dikenali sebagai identitas yang sama. Akun '.$context['label'].' berhasil dihubungkan ke Face Identity yang sudah ada.'
                : 'Wajah '.$context['label'].' berhasil didaftarkan.',
            'data' => [
                'identity_id' => $identity->getKey(),
                'guard' => $context['guard'],
                'model_name' => $identity->model_name,
                'engine' => $identity->engine,
                'engine_version' => $identity->engine_version,
                'embedding_dimension' => $identity->embedding_dimension,
                'enrolled_at' => $identity->enrolled_at,
                'linked_existing_identity' => (bool) ($result['linked_existing_identity'] ?? false),
                'linked_account_count' => (int) ($result['account_count'] ?? 1),
                'link_similarity' => $result['similarity'] ?? null,
            ],
        ], 201);
    }

    public function verify(
        Request $request,
        FaceRecognitionService $faceService,
        FaceIdentityManager $identityManager
    ): JsonResponse {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $account = $request->user();
        $context = $identityManager->context($account);

        if (! $account || ! $context) {
            return $this->unsupportedAccount();
        }

        $identity = $identityManager->identityForAccount($account);
        if (! $identity || ! $identity->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah belum didaftarkan untuk akun ini.',
            ], 409);
        }

        try {
            $result = $faceService->verify($request->file('image'), $identity->embedding);
        } catch (FaceApiException $exception) {
            $status = $exception->isClientError() ? 422 : 503;

            return response()->json([
                'success' => false,
                'message' => $status === 422
                    ? 'Gambar wajah tidak valid untuk verifikasi.'
                    : 'Layanan verifikasi wajah sedang tidak tersedia.',
            ], $status);
        }

        $verified = (bool) ($result['verified'] ?? false);
        if ($verified) {
            $identity->forceFill(['last_verified_at' => now()])->save();
        }

        return response()->json([
            'success' => true,
            'verified' => $verified,
            'similarity' => $result['similarity'] ?? null,
            'threshold' => $result['threshold'] ?? null,
        ]);
    }

    public function destroy(Request $request, FaceIdentityManager $identityManager): JsonResponse
    {
        $account = $request->user();
        $context = $identityManager->context($account);

        if (! $account || ! $context) {
            return $this->unsupportedAccount();
        }

        $result = $identityManager->unlinkAccount($account);

        return response()->json([
            'success' => true,
            'message' => $result['removed']
                ? 'Face Login akun '.$context['label'].' berhasil dilepas dari identitas wajah.'
                : 'Akun ini tidak memiliki Face Login aktif.',
            'data' => $result,
        ]);
    }

    private function statusPayload(?FaceIdentity $identity, bool $setupRequired = false): array
    {
        return [
            'enrolled' => (bool) ($identity?->is_active),
            'setup_required' => $setupRequired,
            'identity_id' => $identity?->getKey(),
            'model_name' => $identity?->model_name,
            'engine' => $identity?->engine,
            'engine_version' => $identity?->engine_version,
            'enrolled_at' => $identity?->enrolled_at,
            'last_verified_at' => $identity?->last_verified_at,
            'linked_account_count' => $identity?->accounts()->count() ?? 0,
        ];
    }

    private function identityTablesReady(): bool
    {
        return Schema::hasTable('face_identities') && Schema::hasTable('face_identity_accounts');
    }

    private function unsupportedAccount(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Tipe akun tidak mendukung Face Recognition.',
        ], 403);
    }
}
