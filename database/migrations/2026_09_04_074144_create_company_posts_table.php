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
        Schema::create('company_posts', function (Blueprint $table) {
            $table->id();

            // Judul informasi / pengumuman / artikel
            $table->string('title');

            // Jenis konten
            // announcement = Pengumuman
            // update       = Update
            // article      = Artikel
            // hr_info      = Informasi HR
            $table->enum('type', [
                'announcement',
                'update',
                'article',
                'hr_info',
            ])->default('announcement');

            // Isi artikel
            $table->longText('content');

            // Gambar opsional
            $table->string('image')->nullable();

            // Status artikel
            // draft     = belum ditampilkan
            // published = sudah ditampilkan
            $table->enum('status', [
                'draft',
                'published',
            ])->default('draft');

            // User/admin yang membuat artikel
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // created_at dan updated_at
            $table->timestamps();

            // Index untuk pencarian/filter status
            $table->index('status');

            // Index type
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_posts');
    }
};