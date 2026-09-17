<?php

namespace App\Services\WhatsApp\Gateways;

use App\Models\WhatsAppGateway;
use App\Services\WhatsApp\Contracts\WhatsAppGatewayInterface;
use Illuminate\Support\Facades\Http;

class WablasGateway implements WhatsAppGatewayInterface
{
    /**
     * Kirim pesan melalui Wablas.
     */
    public function sendText(
        WhatsAppGateway $gateway,
        string $phone,
        string $text
    ): array {
        if (!$gateway->api_id) {
            return [
                'success' => false,
                'status' => 422,
                'response' => 'API ID Wablas belum diisi.',
            ];
        }

        if (!$gateway->api_key) {
            return [
                'success' => false,
                'status' => 422,
                'response' => 'API Key Wablas belum diisi.',
            ];
        }

        $baseUrl = rtrim(
            $gateway->url ?: 'https://wablas.com',
            '/'
        );

        $url = $baseUrl . '/api/v2/send-message';

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $gateway->api_id . '.' . $gateway->api_key,
                    'Content-Type' => 'application/json',
                ])
                ->asJson()
                ->post($url, [
                    'data' => [
                        [
                            'phone' => $phone,
                            'message' => $text,
                            'isGroup' => 'false',
                        ],
                    ],
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
     * Test konfigurasi Wablas.
     *
     * Tidak mengirim pesan.
     */
    public function test(
        WhatsAppGateway $gateway
    ): array {
        if (!$gateway->api_id) {
            return [
                'success' => false,
                'status' => 422,
                'response' => 'API ID Wablas belum diisi.',
            ];
        }

        if (!$gateway->api_key) {
            return [
                'success' => false,
                'status' => 422,
                'response' => 'API Key Wablas belum diisi.',
            ];
        }

        return [
            'success' => true,
            'status' => 200,
            'response' => 'API ID dan API Key Wablas tersedia. Gateway siap digunakan.',
        ];
    }
}