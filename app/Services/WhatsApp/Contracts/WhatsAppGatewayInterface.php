<?php

namespace App\Services\WhatsApp\Contracts;

use App\Models\WhatsAppGateway;

interface WhatsAppGatewayInterface
{
    /**
     * Kirim pesan WhatsApp text.
     */
    public function sendText(
        WhatsAppGateway $gateway,
        string $phone,
        string $text
    ): array;

    /**
     * Test koneksi/configuration gateway.
     */
    public function test(
        WhatsAppGateway $gateway
    ): array;
}