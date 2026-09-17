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
        Schema::create('operational_expenses', function (Blueprint $table) {
            $table->id();

            // Finance yang membuat transaksi
            $table->foreignId('finance_id')
                ->nullable()
                ->constrained('finances')
                ->nullOnDelete();

            // Tanggal transaksi
            $table->date('expense_date');

            // Periode pengeluaran, contoh: 2026-08
            $table->string('period', 7);

            // Kategori pengeluaran
            // Contoh: makan_bersama, uang_makan_lembur, lainnya
            $table->string('category');

            // Keterangan transaksi
            $table->text('description')->nullable();

            // Total nominal transaksi
            $table->decimal('amount', 15, 2);

            // Penerima:
            // all      = semua teknisi
            // selected = beberapa teknisi
            $table->enum('recipient_type', [
                'all',
                'selected',
            ])->default('selected');

            // Bukti transaksi
            // Contoh: operational-expenses/xxxxx.jpg
            $table->string('proof_file')->nullable();

            $table->timestamps();

            // Index untuk laporan bulanan
            $table->index('expense_date');
            $table->index('period');
            $table->index('category');
            $table->index('recipient_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_expenses');
    }
};