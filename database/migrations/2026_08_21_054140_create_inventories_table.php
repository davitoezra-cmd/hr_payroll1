<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();

            // Kode unik inventaris
            $table->string('inventory_code')->unique();

            // Informasi barang
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('description')->nullable();

            // Jumlah barang
            $table->unsignedInteger('quantity')->default(1);

            // Kondisi barang
            $table->enum('condition', [
                'good',
                'minor_damage',
                'major_damage',
                'broken',
            ])->default('good');

            // Lokasi barang
            $table->string('location')->nullable();

            // Status barang
            $table->enum('status', [
                'available',
                'in_use',
                'maintenance',
                'unavailable',
            ])->default('available');

            // Foto barang
            $table->string('image')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index untuk pencarian/filter
            $table->index('category');
            $table->index('condition');
            $table->index('status');
            $table->index('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};