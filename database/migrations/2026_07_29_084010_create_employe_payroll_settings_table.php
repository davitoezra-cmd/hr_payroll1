<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_payroll_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->unique()
                ->constrained('employees')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Gaji Pokok
            |--------------------------------------------------------------------------
            */
            $table->decimal('gaji_harian', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Jam Absensi Employee
            |--------------------------------------------------------------------------
            */
            // jam mulai absensi
            $table->time('jam_masuk')
                ->nullable();

            // batas terakhir tidak dianggap terlambat
            $table->time('batas_telat')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Bonus Datang Lebih Awal
            |--------------------------------------------------------------------------
            */
            // contoh: bonus jika datang sebelum jam 07:30
            $table->time('mulai_bonus_datang')
                ->nullable();

            $table->decimal('bonus_datang_awal', 15, 2)
                ->default(0);

             $table->decimal('bonus_kedisiplinan', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Pulang & Lembur Otomatis
            |--------------------------------------------------------------------------
            */
            // jam pulang normal employee
            $table->time('jam_pulang')
                ->nullable();

            // mulai jam yang dihitung lembur
            $table->time('mulai_lembur')
                ->nullable();

            // tarif lembur per jam
            $table->decimal('tarif_lembur', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Denda Keterlambatan
            |--------------------------------------------------------------------------
            */
            // denda telat
            // bisa per menit atau per kejadian
            $table->decimal('potongan_terlambat', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Hari Libur & Cuti
            |--------------------------------------------------------------------------
            */
            // hari libur tanpa potong gaji
            $table->unsignedTinyInteger('jatah_hari_libur')
                ->default(0);

            // cuti tahunan
            $table->unsignedTinyInteger('jatah_cuti')
                ->default(12);

            /*
            |--------------------------------------------------------------------------
            | Izin & Cuti Potongan
            |--------------------------------------------------------------------------
            */
            $table->decimal('potongan_izin', 15, 2)
                ->default(0);

            $table->decimal('potongan_cuti', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Tanggal Gajian
            |--------------------------------------------------------------------------
            */
            $table->unsignedTinyInteger('tanggal_gajian')
                ->default(25);

            /*
            |--------------------------------------------------------------------------
            | Status Setting
            |--------------------------------------------------------------------------
            */
            $table->boolean('aktif')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_payroll_settings');
    }
};