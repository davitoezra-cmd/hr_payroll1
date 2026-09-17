<?php

namespace App\Services;

use App\Models\WhatsAppGateway;
use App\Services\WhatsApp\WhatsAppGatewayManager;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function __construct(
        protected WhatsAppGatewayManager $gatewayManager
    ) {
    }

    /**
     * Kirim pesan menggunakan gateway aktif.
     */
    public function send(
        string $phone,
        string $text
    ): array {
        $gateway = $this->gatewayManager->active();

        if (!$gateway) {
            return [
                'success' => false,
                'status' => 503,
                'response' => 'Tidak ada WhatsApp Gateway yang aktif.',
            ];
        }

        return $this->sendUsingGateway(
            $gateway,
            $phone,
            $text
        );
    }

    /**
     * Kirim pesan menggunakan gateway tertentu.
     *
     * Digunakan untuk:
     * - Test Send
     * - Pengiriman khusus
     * - Gateway aktif
     */
    public function sendUsingGateway(
        WhatsAppGateway $gateway,
        string $phone,
        string $text
    ): array {
        $phone = $this->normalizePhone($phone);

        if ($phone === '') {
            return [
                'success' => false,
                'status' => 422,
                'response' => 'Nomor WhatsApp tidak valid.',
            ];
        }

        if (trim($text) === '') {
            return [
                'success' => false,
                'status' => 422,
                'response' => 'Pesan WhatsApp tidak boleh kosong.',
            ];
        }

        try {
            $driver = $this->gatewayManager->driver($gateway);

            $result = $driver->sendText(
                $gateway,
                $phone,
                $text
            );

            Log::info('WhatsApp Message Result', [
                'gateway_id' => $gateway->id,
                'gateway_name' => $gateway->name,
                'provider' => $gateway->provider,
                'phone' => $phone,
                'success' => $result['success'] ?? false,
                'status' => $result['status'] ?? null,
            ]);

            return [
                'success' => (bool) ($result['success'] ?? false),
                'status' => $result['status'] ?? 500,
                'response' => $result['response'] ?? null,

                'gateway' => [
                    'id' => $gateway->id,
                    'name' => $gateway->name,
                    'provider' => $gateway->provider,
                ],
            ];

        } catch (\Throwable $e) {

            Log::error('WhatsApp Service Error', [
                'gateway_id' => $gateway->id,
                'provider' => $gateway->provider,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 500,
                'response' => $e->getMessage(),

                'gateway' => [
                    'id' => $gateway->id,
                    'name' => $gateway->name,
                    'provider' => $gateway->provider,
                ],
            ];
        }
    }

    /**
     * Test koneksi gateway.
     */
    public function testGateway(
        ?WhatsAppGateway $gateway = null
    ): array {
        try {
            $gateway = $gateway ?: $this->gatewayManager->active();

            if (!$gateway) {
                return [
                    'success' => false,
                    'status' => 503,
                    'response' => 'Tidak ada WhatsApp Gateway yang aktif.',
                ];
            }

            $driver = $this->gatewayManager->driver($gateway);

            $result = $driver->test($gateway);

            return [
                'success' => (bool) ($result['success'] ?? false),
                'status' => $result['status'] ?? 500,
                'response' => $result['response'] ?? null,

                'gateway' => [
                    'id' => $gateway->id,
                    'name' => $gateway->name,
                    'provider' => $gateway->provider,
                ],
            ];

        } catch (\Throwable $e) {

            Log::error('WhatsApp Gateway Test Error', [
                'gateway_id' => $gateway?->id,
                'provider' => $gateway?->provider,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 500,
                'response' => $e->getMessage(),
            ];
        }
    }

    /**
     * Normalisasi nomor Indonesia.
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (!$phone) {
            return '';
        }

        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }

        if (str_starts_with($phone, '62')) {
            return $phone;
        }

        return $phone;
    }
}

