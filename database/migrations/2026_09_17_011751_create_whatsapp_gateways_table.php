<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_gateways', function (Blueprint $table) {
            $table->id();

            // Nama pengaturan gateway
            $table->string('name')->default('WhatsApp Gateway');

            /*
             * Jenis/provider WhatsApp Gateway.
             *
             * Provider yang didukung:
             * - waha
             * - fonnte
             * - wablas
             * - custom
             */
            $table->string('provider')->default('custom');

            // URL API WhatsApp Gateway
            $table->string('url');

            /*
             * Untuk provider berbeda, field ini digunakan
             * sesuai kebutuhan provider.
             *
             * WAHA:
             * api_id  = session
             *
             * Fonnte:
             * api_id  = tidak digunakan
             *
             * Wablas:
             * api_id  = token
             */
            $table->string('api_id')->nullable();

            /*
             * API Key / Token / Secret Key.
             *
             * WAHA:
             * api_key = X-Api-Key
             *
             * Fonnte:
             * api_key = Authorization token
             *
             * Wablas:
             * api_key = secret key
             */
            $table->text('api_key')->nullable();

            // Apakah gateway sedang digunakan
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            /*
             * Index untuk pencarian gateway aktif.
             */
            $table->index('provider');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_gateways');
    }
};

