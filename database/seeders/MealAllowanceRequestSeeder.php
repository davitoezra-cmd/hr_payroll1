<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\MealAllowanceRequest;
use App\Models\User;
use Carbon\Carbon;

class MealAllowanceRequestSeeder extends Seeder
{
    /**
     * =========================================================
     * SEEDER UANG MAKAN UNTUK SEMUA EMPLOYEE
     * =========================================================
     *
     * Rule testing:
     * - Semua employee akan mendapatkan data uang makan.
     * - Status langsung APPROVED agar langsung masuk payroll.
     * - Uang makan dibuat beberapa kali dalam satu periode.
     * - Nominal dapat disesuaikan.
     * - Tidak membuat duplicate jika seeder dijalankan ulang.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PERIODE TESTING
        |--------------------------------------------------------------------------
        |
        | Silakan ubah bulan dan tahun sesuai payroll yang ingin dites.
        |
        | Contoh:
        | 8  = Agustus
        | 2026 = tahun 2026
        |
        */

        $bulan = 9;
        $tahun = 2026;

        /*
        |--------------------------------------------------------------------------
        | NOMINAL UANG MAKAN
        |--------------------------------------------------------------------------
        */

        $nominalUangMakan = 25000;

        /*
        |--------------------------------------------------------------------------
        | CARI ADMIN / USER UNTUK approved_by
        |--------------------------------------------------------------------------
        |
        | approved_by wajib mengarah ke users.id.
        |
        | Kita ambil user pertama yang tersedia.
        |
        */

        $approvedBy = User::query()->first();

        if (!$approvedBy) {
            $this->command->error(
                'Tidak ada data user di tabel users.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | TENTUKAN TANGGAL UANG MAKAN
        |--------------------------------------------------------------------------
        |
        | Untuk testing kita buat 5 pengajuan uang makan
        | untuk setiap employee.
        |
        | 01, 05, 10, 15, 20 pada bulan yang dipilih.
        |
        */

        $tanggalUangMakan = [
            1,
            5,
            10,
            15,
            20,
        ];

        /*
        |--------------------------------------------------------------------------
        | AMBIL SEMUA EMPLOYEE
        |--------------------------------------------------------------------------
        */

        $employees = Employee::query()
            ->orderBy('id')
            ->get();

        if ($employees->isEmpty()) {
            $this->command->warn(
                'Tidak ada employee yang ditemukan.'
            );

            return;
        }

        $totalCreated = 0;

        /*
        |--------------------------------------------------------------------------
        | BUAT DATA UANG MAKAN UNTUK SEMUA EMPLOYEE
        |--------------------------------------------------------------------------
        */

        foreach ($employees as $employee) {

            foreach ($tanggalUangMakan as $tanggal) {

                $mealDate = Carbon::create(
                    $tahun,
                    $bulan,
                    $tanggal,
                    0,
                    0,
                    0,
                    'Asia/Jakarta'
                )->toDateString();

                /*
                |--------------------------------------------------------------------------
                | CEK DUPLIKAT
                |--------------------------------------------------------------------------
                |
                | Supaya seeder aman dijalankan berkali-kali.
                |
                */

                $exists = MealAllowanceRequest::query()
                    ->where('employee_id', $employee->id)
                    ->whereDate('meal_date', $mealDate)
                    ->exists();

                if ($exists) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | BUAT PENGAJUAN UANG MAKAN
                |--------------------------------------------------------------------------
                |
                | Status langsung approved supaya masuk payroll.
                |
                */

                MealAllowanceRequest::create([
                    'employee_id' => $employee->id,

                    'meal_date' => $mealDate,

                    'amount' => $nominalUangMakan,

                    'reason' =>
                        'Seeder testing payroll - uang makan',

                    'status' => 'approved',

                    'approved_by' => $approvedBy->id,

                    'approved_at' => Carbon::now(
                        'Asia/Jakarta'
                    ),
                ]);

                $totalCreated++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | HASIL SEEDER
        |--------------------------------------------------------------------------
        */

        $totalEmployee = $employees->count();

        $totalUangMakan = $totalCreated * $nominalUangMakan;

        $this->command->info(
            "Seeder uang makan berhasil."
        );

        $this->command->info(
            "Employee ditemukan : {$totalEmployee}"
        );

        $this->command->info(
            "Data dibuat        : {$totalCreated}"
        );

        $this->command->info(
            "Nominal per data   : Rp " .
            number_format(
                $nominalUangMakan,
                0,
                ',',
                '.'
            )
        );

        $this->command->info(
            "Total nominal      : Rp " .
            number_format(
                $totalUangMakan,
                0,
                ',',
                '.'
            )
        );

        $this->command->info(
            "Periode            : {$bulan}/{$tahun}"
        );
    }
}