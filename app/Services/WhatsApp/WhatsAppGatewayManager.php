<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppGateway;
use App\Services\WhatsApp\Contracts\WhatsAppGatewayInterface;
use App\Services\WhatsApp\Gateways\CustomGateway;
use App\Services\WhatsApp\Gateways\FonnteGateway;
use App\Services\WhatsApp\Gateways\WablasGateway;
use App\Services\WhatsApp\Gateways\WahaGateway;
use InvalidArgumentException;

class WhatsAppGatewayManager
{
    /**
     * Ambil driver berdasarkan provider gateway.
     */
    public function driver(
        WhatsAppGateway $gateway
    ): WhatsAppGatewayInterface {
        return match (strtolower($gateway->provider)) {
            'waha' => app(WahaGateway::class),
            'fonnte' => app(FonnteGateway::class),
            'wablas' => app(WablasGateway::class),
            'custom' => app(CustomGateway::class),

            default => throw new InvalidArgumentException(
                "WhatsApp provider '{$gateway->provider}' tidak didukung."
            ),
        };
    }

    /**
     * Ambil gateway WhatsApp yang sedang aktif.
     */
    public function active(): ?WhatsAppGateway
    {
        return WhatsAppGateway::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }
}