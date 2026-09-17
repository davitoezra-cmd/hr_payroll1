<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeShiftSchedule;
use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeShiftScheduleSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * =========================================================
         * TIMEZONE
         * =========================================================
         */
        $timezone = 'Asia/Jakarta';

        /**
         * =========================================================
         * AMBIL EMPLOYEE
         * =========================================================
         */
        $employees = Employee::query()
            ->orderBy('id')
            ->get();

        /**
         * =========================================================
         * AMBIL SHIFT PAGI
         * =========================================================
         *
         * Untuk demo kita sengaja menggunakan satu shift saja
         * supaya alur video mudah dijelaskan.
         */
        $shift = WorkShift::query()
            ->where('code', 'PAGI')
            ->where('is_active', true)
            ->first();

        if ($employees->isEmpty()) {
            $this->command?->warn(
                'Shift schedule dilewati karena employee belum tersedia.'
            );

            return;
        }

        if (!$shift) {
            $this->command?->warn(
                'Shift PAGI belum tersedia. Jalankan WorkShiftSeeder terlebih dahulu.'
            );

            return;
        }

        /**
         * =========================================================
         * PERIODE
         * =========================================================
         *
         * Membuat schedule untuk:
         *
         * 1 bulan penuh.
         *
         * Contoh September 2026:
         *
         * 01-09-2026
         * sampai
         * 30-09-2026
         */
        $startDate = Carbon::now($timezone)
            ->startOfMonth();

        $endDate = Carbon::now($timezone)
            ->endOfMonth();

        /**
         * =========================================================
         * TRANSACTION
         * =========================================================
         */
        DB::transaction(function () use (
            $employees,
            $shift,
            $startDate,
            $endDate
        ) {

            foreach ($employees as $employee) {

                $date = $startDate->copy();

                while ($date->lte($endDate)) {

                    /**
                     * =================================================
                     * HARI KERJA
                     * =================================================
                     *
                     * Senin - Sabtu = kerja
                     * Minggu         = libur
                     *
                     * Kalau perusahaan kamu tetap bekerja hari Minggu,
                     * bagian ini bisa diubah.
                     */
                    $isSunday = $date->dayOfWeek === Carbon::SUNDAY;

                    if ($isSunday) {

                        EmployeeShiftSchedule::updateOrCreate(
                            [
                                'employee_id' => $employee->id,
                                'work_date'   => $date->toDateString(),
                            ],
                            [
                                'shift_id'    => null,
                                'status'      => 'off',
                                'notes'       => 'Libur hari Minggu',
                                'assigned_by' => null,
                            ]
                        );

                    } else {

                        /**
                         * =================================================
                         * HARI KERJA
                         * =================================================
                         */
                        EmployeeShiftSchedule::updateOrCreate(
                            [
                                'employee_id' => $employee->id,
                                'work_date'   => $date->toDateString(),
                            ],
                            [
                                'shift_id'    => $shift->id,
                                'status'      => 'work',
                                'notes'       => 'Jadwal kerja demo',
                                'assigned_by' => null,
                            ]
                        );
                    }

                    $date->addDay();
                }
            }
        });

        /**
         * =========================================================
         * HASIL
         * =========================================================
         */
        $this->command?->newLine();

        $this->command?->info(
            '=============================================='
        );

        $this->command?->info(
            'EMPLOYEE SHIFT SCHEDULE SEEDER BERHASIL'
        );

        $this->command?->info(
            '=============================================='
        );

        $this->command?->info(
            'Jumlah Employee : ' . $employees->count()
        );

        $this->command?->info(
            'Shift           : ' . $shift->name
        );

        $this->command?->info(
            'Jam Kerja       : ' .
            $shift->jam_masuk .
            ' - ' .
            $shift->jam_pulang
        );

        $this->command?->info(
            'Toleransi Telat  : ' .
            $shift->late_tolerance_minutes .
            ' menit'
        );

        $this->command?->info(
            'Periode         : ' .
            $startDate->format('d-m-Y') .
            ' s/d ' .
            $endDate->format('d-m-Y')
        );

        $this->command?->info(
            'Minggu          : Libur'
        );

        $this->command?->info(
            '=============================================='
        );

        $this->command?->newLine();
    }
}

