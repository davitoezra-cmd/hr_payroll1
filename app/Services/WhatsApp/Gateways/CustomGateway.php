<?php

namespace App\Services\WhatsApp\Gateways;

use App\Models\WhatsAppGateway;
use App\Services\WhatsApp\Contracts\WhatsAppGatewayInterface;
use Illuminate\Support\Facades\Http;

class CustomGateway implements WhatsAppGatewayInterface
{
    /**
     * Kirim pesan text menggunakan custom gateway.
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
                'response' => 'URL Custom WhatsApp Gateway belum diisi.',
            ];
        }

        $response = Http::timeout(30)
            ->asForm()
            ->post($gateway->url, [
                'phone' => $phone,
                'api_id' => $gateway->api_id,
                'api_key' => $gateway->api_key,
                'text' => $text,
            ]);

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'response' => $response->json() ?? $response->body(),
        ];
    }

    /**
     * Test Custom Gateway.
     */
    public function test(
        WhatsAppGateway $gateway
    ): array {
        if (!$gateway->url) {
            return [
                'success' => false,
                'status' => 422,
                'response' => 'URL Custom WhatsApp Gateway belum diisi.',
            ];
        }

        try {
            $response = Http::timeout(15)
                ->get($gateway->url);

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