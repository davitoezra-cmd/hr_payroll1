<?php

namespace App\Services\WhatsApp\Gateways;

use App\Models\WhatsAppGateway;
use App\Services\WhatsApp\Contracts\WhatsAppGatewayInterface;
use Illuminate\Support\Facades\Http;

class WahaGateway implements WhatsAppGatewayInterface
{
    /**
     * Kirim pesan text melalui WAHA.
     */
    public function sendText(
        WhatsAppGateway $gateway,
        string $phone,
        string $text
    ): array {
        if (!$gateway->url) {
            return [
                'success' => false,
                'status' => 422,
                'response' => 'URL WAHA belum diisi.',
            ];
        }

        $baseUrl = rtrim($gateway->url, '/');

        $url = $baseUrl . '/api/sendText';

        $session = $gateway->api_id ?: 'default';

        try {
            $request = Http::timeout(30);

            if ($gateway->api_key) {
                $request = $request->withHeaders([
                    'X-Api-Key' => $gateway->api_key,
                ]);
            }

            $response = $request
                ->asJson()
                ->post($url, [
                    'session' => $session,
                    'chatId' => $phone . '@c.us',
                    'text' => $text,
                ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 500,
                'response' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test koneksi WAHA.
     *
     * Mengambil daftar session WAHA.
     */
    public function test(
        WhatsAppGateway $gateway
    ): array {
        if (!$gateway->url) {
            return [
                'success' => false,
                'status' => 422,
                'response' => 'URL WAHA belum diisi.',
            ];
        }

        $baseUrl = rtrim($gateway->url, '/');

        $url = $baseUrl . '/api/sessions';

        try {
            $request = Http::timeout(15);

            if ($gateway->api_key) {
                $request = $request->withHeaders([
                    'X-Api-Key' => $gateway->api_key,
                ]);
            }

            $response = $request->get($url);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 500,
                'response' => $e->getMessage(),
            ];
        }
    }
}