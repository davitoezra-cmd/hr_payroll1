<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeePayrollSetting;
use Illuminate\Database\Seeder;

class EmployeePayrollSettingSeeder extends Seeder
{
    public function run(): void
    {
        Employee::query()->each(function (Employee $employee) {

            EmployeePayrollSetting::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                ],
                [
                    // ==========================================
                    // GAJI DASAR
                    // ==========================================
                    'gaji_harian' => 150000,

                    // ==========================================
                    // JAM ABSENSI
                    // ==========================================
                    //
                    // Jam masuk normal employee.
                    //
                    'jam_masuk' => '08:00:00',

                    //
                    // Batas toleransi keterlambatan.
                    //
                    'batas_telat' => '08:10:00',

                    // ==========================================
                    // BONUS DATANG LEBIH AWAL
                    // ==========================================
                    //
                    // Jika datang SEBELUM 08:00
                    // maka mendapat bonus.
                    //
                    // Contoh:
                    //
                    // 07:50 -> bonus
                    // 07:55 -> bonus
                    // 07:59 -> bonus
                    // 08:00 -> tidak bonus
                    // 08:05 -> tidak bonus
                    //
                    'mulai_bonus_datang' => '08:00:00',

                    'bonus_datang_awal' => 10000,

                    // ==========================================
                    // BONUS KEDISIPLINAN
                    // ==========================================
                    //
                    // Diberikan 1x per periode jika:
                    //
                    // - periode sudah selesai
                    // - memiliki attendance
                    // - tidak pernah terlambat
                    //
                    'bonus_kedisiplinan' => 100000,

                    // ==========================================
                    // JAM PULANG
                    // ==========================================
                    'jam_pulang' => '17:00:00',

                    // ==========================================
                    // MULAI LEMBUR
                    // ==========================================
                    'mulai_lembur' => '17:00:00',

                    // ==========================================
                    // TARIF LEMBUR
                    // ==========================================
                    'tarif_lembur' => 25000,

                    // ==========================================
                    // DENDA TERLAMBAT PER MENIT
                    // ==========================================
                    //
                    // Rp1.000 / menit.
                    //
                    // 1 menit  = Rp1.000
                    // 10 menit = Rp10.000
                    // 20 menit = Rp20.000
                    // 60 menit = Rp60.000
                    //
                    'potongan_terlambat' => 1000,

                    // ==========================================
                    // POTONGAN IZIN
                    // ==========================================
                    'potongan_izin' => 150000,

                    // ==========================================
                    // POTONGAN CUTI
                    // ==========================================
                    'potongan_cuti' => 150000,

                    // ==========================================
                    // JATAH HARI LIBUR
                    // ==========================================
                    'jatah_hari_libur' => 0,

                    // ==========================================
                    // JATAH CUTI
                    // ==========================================
                    'jatah_cuti' => 12,

                    // ==========================================
                    // TANGGAL GAJIAN
                    // ==========================================
                    'tanggal_gajian' => 25,

                    // ==========================================
                    // STATUS
                    // ==========================================
                    'aktif' => true,
                ]
            );
        });
    }
}