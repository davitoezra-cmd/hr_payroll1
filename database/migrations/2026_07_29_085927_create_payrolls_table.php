<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Periode Payroll
            |--------------------------------------------------------------------------
            */
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');

            /*
            |--------------------------------------------------------------------------
            | Gaji Harian
            |--------------------------------------------------------------------------
            */
            // Tarif gaji per hari
            $table->decimal('gaji_harian', 15, 2)->default(0);

            // Total gaji dari jumlah hadir
            $table->decimal('total_gaji_dasar', 15, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Bonus
            |--------------------------------------------------------------------------
            */
            $table->decimal('bonus_datang_awal', 15, 2)->default(0);

           
            /*
            |--------------------------------------------------------------------------
            | Potongan
            |--------------------------------------------------------------------------
            */
            // Gabungan potongan telat + izin + cuti
            $table->decimal('total_potongan', 15, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Kasbon
            |--------------------------------------------------------------------------
            */
            $table->decimal('total_kasbon', 15, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Koreksi Payroll
            |--------------------------------------------------------------------------
            */
            // Bisa bernilai positif ataupun negatif
            $table->decimal('total_koreksi', 15, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Total Gaji Bersih
            |--------------------------------------------------------------------------
            */
            $table->decimal('take_home_pay', 15, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Ringkasan Absensi
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('total_hadir')->default(0);
            $table->unsignedInteger('total_terlambat')->default(0);
            $table->unsignedInteger('total_izin')->default(0);
            $table->unsignedInteger('total_cuti')->default(0);
            $table->unsignedInteger('total_sakit')->default(0);
            $table->unsignedInteger('total_uang_makan')->default(0);

            $table->decimal('potongan_terlambat', 15, 2)->default(0);
            $table->decimal('potongan_izin', 15, 2)->default(0);
            $table->decimal('potongan_cuti', 15, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Status Payroll
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'draft',
                'generated',
                'paid'
            ])->default('draft');

              $table->enum('payment_method', [
                'cash',
                'transfer',
            ])->nullable();
        

            /*
            |--------------------------------------------------------------------------
            | Finance
            |--------------------------------------------------------------------------
            */
            $table->foreignId('finance_id')
                ->nullable()
                ->constrained('finances')
                ->nullOnDelete();

            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Satu Payroll per Employee per Bulan
            |--------------------------------------------------------------------------
            */
            $table->unique([
                'employee_id',
                'bulan',
                'tahun'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};