<?php

namespace App\Services\WhatsApp\Gateways;

use App\Models\WhatsAppGateway;
use App\Services\WhatsApp\Contracts\WhatsAppGatewayInterface;
use Illuminate\Support\Facades\Http;

class FonnteGateway implements WhatsAppGatewayInterface
{
    /**
     * Kirim pesan melalui Fonnte.
     */
    public function sendText(
        WhatsAppGateway $gateway,
        string $phone,
        string $text
    ): array {
        if (!$gateway->api_key) {
            return [
                'success' => false,
                'status' => 422,
                'response' => 'API Key Fonnte belum diisi.',
            ];
        }

        $url = $gateway->url ?: 'https://api.fonnte.com/send';

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $gateway->api_key,
                ])
                ->asForm()
                ->post($url, [
                    'target' => $phone,
                    'message' => $text,
                    'countryCode' => '62',
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
     * Test konfigurasi Fonnte.
     *
     * Tidak mengirim pesan.
     */
    public function test(
        WhatsAppGateway $gateway
    ): array {
        if (!$gateway->api_key) {
            return [
                'success' => false,
                'status' => 422,
                'response' => 'API Key Fonnte belum diisi.',
            ];
        }

        return [
            'success' => true,
            'status' => 200,
            'response' => 'API Key Fonnte tersedia. Gateway siap digunakan.',
        ];
    }
}