<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\FaceApiException;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeFaceTemplate;
use App\Services\FaceRecognitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EmployeeFaceController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $employee = $request->user();

        if (! $employee instanceof Employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee authentication required.',
            ], 403);
        }

        // Status "belum terdaftar" adalah kondisi normal. Jika migration face
        // recognition belum dijalankan, jangan lempar HTTP 500 ke frontend.
        if (! Schema::hasTable('employee_face_templates')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'enrolled' => false,
                    'setup_required' => true,
                    'model_name' => null,
                    'engine' => null,
                    'engine_version' => null,
                    'enrolled_at' => null,
                    'last_verified_at' => null,
                ],
                'message' => 'Face Recognition belum siap. Jalankan migration database terlebih dahulu.',
            ]);
        }

        $template = $employee->faceTemplate;

        return response()->json([
            'success' => true,
            'data' => [
                'enrolled' => (bool) ($template?->is_active),
                'setup_required' => false,
                'model_name' => $template?->model_name,
                'engine' => $template?->engine,
                'engine_version' => $template?->engine_version,
                'enrolled_at' => $template?->enrolled_at,
                'last_verified_at' => $template?->last_verified_at,
            ],
        ]);

    }

    public function enroll(
        Request $request,
        FaceRecognitionService $faceService
    ): JsonResponse {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $employee = $request->user();
        if (! $employee instanceof Employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee authentication required.',
            ], 403);
        }

        try {
            $face = $faceService->createEmbedding($request->file('image'));
        } catch (FaceApiException $exception) {
            Log::warning('Face enrollment API failed', [
                'employee_id' => $employee->id,
                'face_api_status' => $exception->apiStatus,
                'error' => $exception->getMessage(),
            ]);

            $status = $exception->isClientError() ? 422 : 503;
            $message = $status === 422
                ? 'Wajah tidak dapat diproses. Pastikan hanya satu wajah terlihat jelas.'
                : 'Layanan verifikasi wajah sedang tidak tersedia.';

            // Pada environment lokal/debug, kirim alasan sebenarnya agar 503
            // tidak menyembunyikan timeout/kegagalan dari FastAPI.
            if (config('app.debug')) {
                $message .= ' Detail: '.$exception->getMessage();
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'face_api_status' => config('app.debug') ? $exception->apiStatus : null,
            ], $status);
        }

        $embedding = $face['embedding'] ?? null;
        if (! is_array($embedding) || empty($embedding)) {
            return response()->json([
                'success' => false,
                'message' => 'Face API tidak mengembalikan embedding yang valid.',
            ], 502);
        }

        $template = EmployeeFaceTemplate::updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'embedding' => $embedding,
                'engine' => (string) ($face['engine'] ?? 'insightface'),
                'engine_version' => $face['engine_version'] ?? null,
                'model_name' => (string) ($face['model_name'] ?? 'unknown'),
                'embedding_dimension' => (int) ($face['dimension'] ?? count($embedding)),
                'is_active' => true,
                'enrolled_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Wajah employee berhasil didaftarkan.',
            'data' => [
                'employee_id' => $employee->id,
                'model_name' => $template->model_name,
                'engine' => $template->engine,
                'engine_version' => $template->engine_version,
                'embedding_dimension' => $template->embedding_dimension,
                'enrolled_at' => $template->enrolled_at,
            ],
        ], 201);
    }

    public function verify(
        Request $request,
        FaceRecognitionService $faceService
    ): JsonResponse {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $employee = $request->user();
        if (! $employee instanceof Employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee authentication required.',
            ], 403);
        }

        $template = $employee->faceTemplate;
        if (! $template || ! $template->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah belum didaftarkan.',
            ], 409);
        }

        try {
            $result = $faceService->verify(
                $request->file('image'),
                $template->embedding
            );
        } catch (FaceApiException $exception) {
            Log::warning('Face verification API failed', [
                'employee_id' => $employee->id,
                'error' => $exception->getMessage(),
            ]);

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
            $template->forceFill(['last_verified_at' => now()])->save();
        }

        return response()->json([
            'success' => true,
            'verified' => $verified,
            'similarity' => $result['similarity'] ?? null,
            'threshold' => $result['threshold'] ?? null,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $employee = $request->user();

        if (! $employee instanceof Employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee authentication required.',
            ], 403);
        }

        $employee->faceTemplate()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Face template berhasil dihapus.',
        ]);
    }
}
