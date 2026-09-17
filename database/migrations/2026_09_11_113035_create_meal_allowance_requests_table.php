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
        Schema::create('meal_allowance_requests', function (Blueprint $table) {

            $table->id();

            // Karyawan yang mengajukan
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            // Tanggal uang makan
            $table->date('meal_date');

            // Nominal uang makan
            $table->decimal('amount', 15, 2);

            // Alasan / keterangan pengajuan
            $table->text('reason')->nullable();

            // Status approval
            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            // Admin yang melakukan approval
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Waktu approval
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_allowance_requests');
    }
};