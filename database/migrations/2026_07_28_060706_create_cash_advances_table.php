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
        Schema::create('cash_advances', function (Blueprint $table) {

            $table->id();

            // =====================================================
            // EMPLOYEE YANG MENGAJUKAN
            // =====================================================
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            // =====================================================
            // PAYROLL YANG MEMOTONG KASBON
            // =====================================================
            //
            // Nullable karena kasbon belum tentu langsung
            // dipotong oleh payroll.
            //
            // Contoh:
            //
            // is_deducted = false
            // payroll_id  = null
            //
            // Setelah masuk payroll:
            //
            // is_deducted = true
            // payroll_id  = ID payroll
            //
           $table->unsignedBigInteger('payroll_id') ->nullable();
            // =====================================================
            // NOMINAL KASBON
            // =====================================================
            $table->decimal('amount', 15, 2);

            // =====================================================
            // ALASAN KASBON
            // =====================================================
            $table->text('reason');

            // =====================================================
            // STATUS APPROVAL
            // =====================================================
            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            // =====================================================
            // ADMIN YANG APPROVE
            // =====================================================
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // =====================================================
            // WAKTU APPROVE
            // =====================================================
            $table->timestamp('approved_at')
                ->nullable();

            // =====================================================
            // CATATAN ADMIN
            // =====================================================
            $table->text('approval_note')
                ->nullable();

            // =====================================================
            // SUDAH DIBAYAR / DITERIMA EMPLOYEE
            // =====================================================
            //
            // true:
            // Kasbon sudah diberikan kepada employee.
            //
            // false:
            // Kasbon belum diberikan.
            //
            $table->boolean('is_paid')
                ->default(false);

            // =====================================================
            // SUDAH DIPOTONG PAYROLL
            // =====================================================
            //
            // true:
            // Kasbon sudah masuk perhitungan potongan payroll.
            //
            // false:
            // Belum masuk payroll.
            //
            $table->boolean('is_deducted')
                ->default(false);

            // =====================================================
            // TANGGAL KASBON DIBAYARKAN / DIPROSES
            // =====================================================
            $table->timestamp('paid_at')
                ->nullable();

            // =====================================================
            // TIMESTAMPS
            // =====================================================
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_advances');
    }
};