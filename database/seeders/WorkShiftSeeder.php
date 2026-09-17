<?php

namespace Database\Seeders;

use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WorkShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [

            /*
            |--------------------------------------------------------------------------
            | SHIFT PAGI
            |--------------------------------------------------------------------------
            */
            [
                'code' => 'PAGI',
                'name' => 'Shift Pagi',

                'jam_masuk' => '08:00:00',
                'jam_pulang' => '17:00:00',

                /*
                 * Karyawan masih dianggap tepat waktu
                 * sampai 08:10.
                 */
                'late_tolerance_minutes' => 10,

                /*
                 * Datang sebelum 07:30 mendapatkan
                 * bonus datang awal jika PayrollController
                 * menggunakan field ini.
                 */
                'mulai_bonus_datang' => '07:30:00',

                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | SHIFT SIANG
            |--------------------------------------------------------------------------
            */
            [
                'code' => 'SIANG',
                'name' => 'Shift Siang',

                'jam_masuk' => '14:00:00',
                'jam_pulang' => '22:00:00',

                'late_tolerance_minutes' => 10,

                /*
                 * Bonus datang awal:
                 * sebelum 13:30.
                 */
                'mulai_bonus_datang' => '13:30:00',

                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | SHIFT MALAM
            |--------------------------------------------------------------------------
            */
            [
                'code' => 'MALAM',
                'name' => 'Shift Malam',

                'jam_masuk' => '22:00:00',
                'jam_pulang' => '06:00:00',

                'late_tolerance_minutes' => 10,

                /*
                 * Bonus datang awal:
                 * sebelum 21:30.
                 */
                'mulai_bonus_datang' => '21:30:00',

                'is_active' => true,
            ],
        ];

        foreach ($shifts as $data) {

            /*
            |--------------------------------------------------------------------------
            | PARSE JAM
            |--------------------------------------------------------------------------
            */
            $start = Carbon::createFromFormat(
                'H:i:s',
                $data['jam_masuk']
            );

            $end = Carbon::createFromFormat(
                'H:i:s',
                $data['jam_pulang']
            );

            /*
            |--------------------------------------------------------------------------
            | CEK SHIFT LINTAS HARI
            |--------------------------------------------------------------------------
            |
            | Contoh:
            |
            | 22:00 -> 06:00
            |
            | berarti lintas hari.
            |
            */
            $lintasHari = $end->lessThanOrEqualTo($start);

            /*
            |--------------------------------------------------------------------------
            | SIMPAN / UPDATE WORK SHIFT
            |--------------------------------------------------------------------------
            */
            WorkShift::updateOrCreate(
                [
                    'code' => $data['code'],
                ],
                [
                    'name' => $data['name'],

                    'jam_masuk' => $data['jam_masuk'],
                    'jam_pulang' => $data['jam_pulang'],

                    /*
                     * Toleransi keterlambatan.
                     */
                    'late_tolerance_minutes' =>
                        $data['late_tolerance_minutes'],

                    /*
                     * Batas waktu setelah toleransi.
                     *
                     * PAGI:
                     * 08:00 + 10 menit = 08:10
                     *
                     * SIANG:
                     * 14:00 + 10 menit = 14:10
                     *
                     * MALAM:
                     * 22:00 + 10 menit = 22:10
                     */
                    'batas_telat' =>
                        $start
                            ->copy()
                            ->addMinutes(
                                $data['late_tolerance_minutes']
                            )
                            ->format('H:i:s'),

                    /*
                     * Mulai bonus datang awal.
                     */
                    'mulai_bonus_datang' =>
                        $data['mulai_bonus_datang'],

                    /*
                     * Lembur dimulai setelah jam pulang.
                     */
                    'mulai_lembur' =>
                        $data['jam_pulang'],

                    /*
                     * Shift malam = lintas hari.
                     */
                    'lintas_hari' =>
                        $lintasHari,

                    'is_active' =>
                        $data['is_active'],
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | INFO
        |--------------------------------------------------------------------------
        */
        $this->command?->newLine();

        $this->command?->info(
            '=============================================='
        );

        $this->command?->info(
            'WORK SHIFT SEEDER BERHASIL'
        );

        $this->command?->info(
            '=============================================='
        );

        $this->command?->info(
            'PAGI  : 08:00 - 17:00 | Toleransi 10 menit | Bonus < 07:30'
        );

        $this->command?->info(
            'SIANG : 14:00 - 22:00 | Toleransi 10 menit | Bonus < 13:30'
        );

        $this->command?->info(
            'MALAM : 22:00 - 06:00 | Toleransi 10 menit | Bonus < 21:30'
        );

        $this->command?->info(
            '=============================================='
        );

        $this->command?->newLine();
    }
}

