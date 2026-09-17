<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Nama dokumen yang ditampilkan
            $table->string('name');

            // Kategori dokumen
            $table->string('category')->nullable();

            // Nama file asli
            $table->string('file_name');

            // Lokasi penyimpanan file
            $table->string('file_path');

            // Ukuran file dalam byte
            $table->unsignedBigInteger('file_size')->nullable();

            // MIME type, contoh: application/pdf
            $table->string('mime_type')->nullable();

            // Deskripsi/keterangan dokumen
            $table->text('description')->nullable();

            // User yang mengupload
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

