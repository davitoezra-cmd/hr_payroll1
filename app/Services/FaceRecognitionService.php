<?php

namespace App\Services;

use App\Exceptions\FaceApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class FaceRecognitionService
{
    public function createEmbedding(UploadedFile $image): array
    {
        return $this->sendImageRequest('/v1/face/embedding', $image);
    }

    public function verify(UploadedFile $image, array $referenceEmbedding): array
    {
        return $this->sendImageRequest('/v1/face/verify', $image, [
            'reference_embedding' => json_encode($referenceEmbedding, JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * Identify one face against many enrolled employee embeddings in a single
     * FastAPI request. Each reference must contain an "id" and "embedding".
     */
    public function identify(UploadedFile $image, array $references): array
    {
        return $this->sendImageRequest('/v1/face/identify', $image, [
            'references' => json_encode($references, JSON_THROW_ON_ERROR),
        ]);
    }

    private function client(): PendingRequest
    {
        $apiKey = (string) config('services.face_api.key');

        return Http::acceptJson()
            ->withHeaders([
                'X-API-Key' => $apiKey,
            ])
            ->connectTimeout((int) config('services.face_api.connect_timeout', 5))
            ->timeout((int) config('services.face_api.timeout', 60));
    }

    private function sendImageRequest(
        string $path,
        UploadedFile $image,
        array $fields = []
    ): array {
        $baseUrl = rtrim((string) config('services.face_api.url'), '/');

        try {
            $request = $this->client()->attach(
                'image',
                fopen($image->getRealPath(), 'r'),
                $image->getClientOriginalName() ?: 'face.jpg',
                ['Content-Type' => $image->getMimeType() ?: 'image/jpeg']
            );

            foreach ($fields as $name => $value) {
                $request = $request->attach($name, (string) $value);
            }

            $response = $request->post($baseUrl.$path);
        } catch (ConnectionException $exception) {
            throw new FaceApiException(
                'Tidak dapat terhubung ke Face API.',
                null,
                $exception
            );
        } catch (\Throwable $exception) {
            if ($exception instanceof FaceApiException) {
                throw $exception;
            }

            throw new FaceApiException(
                'Face API request gagal: '.$exception->getMessage(),
                null,
                $exception
            );
        }

        if (! $response->successful()) {
            $detail = $response->json('detail');
            $message = is_string($detail)
                ? $detail
                : ($response->json('message') ?: 'Face API mengembalikan error.');

            throw new FaceApiException(
                (string) $message,
                $response->status()
            );
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new FaceApiException('Response Face API tidak valid.', 502);
        }

        return $payload;
    }
}
