<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\EmployeeShiftSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
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
         * PERIODE SEED
         * =========================================================
         *
         * Membuat attendance untuk 1 bulan penuh.
         *
         * Jika sekarang September 2026:
         *
         * 01 September 2026
         * sampai
         * 30 September 2026
         */
        $periodStart = Carbon::now($timezone)
            ->startOfMonth();

        $periodEnd = Carbon::now($timezone)
            ->endOfMonth();

        /**
         * =========================================================
         * AMBIL SHIFT SCHEDULE
         * =========================================================
         */
        $schedules = EmployeeShiftSchedule::with([
                'shift',
                'employee',
            ])
            ->where('status', 'work')
            ->whereBetween('work_date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderBy('work_date')
            ->orderBy('employee_id')
            ->get();

        if ($schedules->isEmpty()) {
            $this->command?->warn(
                'Tidak ada Employee Shift Schedule untuk periode ' .
                $periodStart->format('F Y') .
                '.'
            );

            return;
        }

        /**
         * =========================================================
         * STATISTIK
         * =========================================================
         */
        $totalAttendance = 0;
        $totalBonusAwal = 0;
        $totalTepatWaktu = 0;
        $totalTerlambat = 0;
        $totalLembur = 0;

        $admin1Terlambat = 0;

        /**
         * =========================================================
         * LOOP SCHEDULE
         * =========================================================
         */
        foreach ($schedules as $index => $schedule) {

            /**
             * =====================================================
             * VALIDASI SHIFT
             * =====================================================
             */
            if (!$schedule->shift) {
                $this->command?->warn(
                    "Schedule ID {$schedule->id} tidak memiliki shift."
                );

                continue;
            }

            $shift = $schedule->shift;

            /**
             * =====================================================
             * VALIDASI EMPLOYEE
             * =====================================================
             */
            if (!$schedule->employee) {
                $this->command?->warn(
                    "Schedule ID {$schedule->id} tidak memiliki employee."
                );

                continue;
            }

            $employee = $schedule->employee;

            /**
             * =====================================================
             * VALIDASI JAM SHIFT
             * =====================================================
             */
            if (
                !$shift->jam_masuk ||
                !$shift->jam_pulang
            ) {
                $this->command?->warn(
                    "Shift ID {$shift->id} tidak memiliki jam masuk/pulang."
                );

                continue;
            }

            /**
             * =====================================================
             * CEK APAKAH EMPLOYEE ADALAH ADMIN 1
             * =====================================================
             *
             * Hanya Admin 1 yang dibuat memiliki keterlambatan.
             *
             * Employee lain:
             *
             * -> tetap datang lebih awal
             * -> tidak terlambat
             * -> tetap mendapatkan bonus datang awal
             */
            $isAdmin1 = strtolower(trim($employee->name)) === 'admin 1';

            /**
             * =====================================================
             * TANGGAL KERJA
             * =====================================================
             */
            $workDate = Carbon::parse(
                $schedule->work_date,
                $timezone
            )->format('Y-m-d');

            /**
             * =====================================================
             * JAM MASUK SHIFT
             * =====================================================
             */
            $scheduledStart = Carbon::parse(
                $workDate . ' ' . $shift->jam_masuk,
                $timezone
            );

            /**
             * =====================================================
             * JAM PULANG SHIFT
             * =====================================================
             */
            $scheduledEnd = Carbon::parse(
                $workDate . ' ' . $shift->jam_pulang,
                $timezone
            );

            /**
             * =====================================================
             * SHIFT LINTAS HARI
             * =====================================================
             */
            if (
                $shift->lintas_hari ||
                $scheduledEnd->lessThanOrEqualTo($scheduledStart)
            ) {
                $scheduledEnd->addDay();
            }

            /**
             * =====================================================
             * TOLERANSI TELAT
             * =====================================================
             */
            $tolerance = (int) (
                $shift->late_tolerance_minutes ?? 10
            );

            $lateLimit = $scheduledStart
                ->copy()
                ->addMinutes($tolerance);

            /**
             * =====================================================
             * CHECK-IN
             * =====================================================
             *
             * ADMIN 1:
             *
             * Kita sengaja buat beberapa hari terlambat.
             *
             * Hari tertentu:
             *
             * 08:00 = tepat waktu
             * 08:15 = telat 15 menit
             * 08:25 = telat 25 menit
             * 08:05 = masih dalam toleransi
             * 08:20 = telat 20 menit
             *
             * EMPLOYEE LAIN:
             *
             * Tetap datang 30 menit lebih awal.
             */

            $dayOfMonth = Carbon::parse(
                $workDate,
                $timezone
            )->day;

            if ($isAdmin1) {

                /**
                 * =================================================
                 * ADMIN 1 - POLA TELAT
                 * =================================================
                 *
                 * Setiap 5 hari:
                 *
                 * Hari 1 -> tepat waktu
                 * Hari 2 -> telat 15 menit
                 * Hari 3 -> telat 25 menit
                 * Hari 4 -> datang 5 menit lebih awal
                 * Hari 5 -> telat 20 menit
                 *
                 * Lalu mengulang.
                 */

                $pattern = (($dayOfMonth - 1) % 5) + 1;

                switch ($pattern) {

                    case 2:
                        // Telat 15 menit
                        $checkInAt = $scheduledStart
                            ->copy()
                            ->addMinutes(15);
                        break;

                    case 3:
                        // Telat 25 menit
                        $checkInAt = $scheduledStart
                            ->copy()
                            ->addMinutes(25);
                        break;

                    case 4:
                        // Datang 5 menit lebih awal
                        $checkInAt = $scheduledStart
                            ->copy()
                            ->subMinutes(5);
                        break;

                    case 5:
                        // Telat 20 menit
                        $checkInAt = $scheduledStart
                            ->copy()
                            ->addMinutes(20);
                        break;

                    default:
                        // Tepat waktu
                        $checkInAt = $scheduledStart
                            ->copy();
                        break;
                }

            } else {

                /**
                 * =================================================
                 * EMPLOYEE LAIN
                 * =================================================
                 *
                 * Semua employee selain Admin 1 tetap datang
                 * 30 menit lebih awal.
                 */
                $checkInAt = $scheduledStart
                    ->copy()
                    ->subMinutes(30);

                /**
                 * Jangan sampai tanggal berubah.
                 */
                if (
                    $checkInAt->format('Y-m-d') !== $workDate
                ) {
                    $checkInAt = $scheduledStart
                        ->copy()
                        ->subMinutes(5);
                }
            }

            /**
             * =====================================================
             * HITUNG STATUS
             * =====================================================
             *
             * Status:
             *
             * - hadir
             * - terlambat
             *
             * Admin 1 yang melewati batas toleransi
             * akan dianggap terlambat.
             */

            $actualLateMinutes = 0;

            if (
                $checkInAt->greaterThan($lateLimit)
            ) {
                $actualLateMinutes = $lateLimit
                    ->diffInMinutes($checkInAt);
            }

            if ($actualLateMinutes > 0) {
                $status = 'terlambat';
            } else {
                $status = 'hadir';
            }

            /**
             * =====================================================
             * CHECK-OUT
             * =====================================================
             *
             * Sebagian attendance dibuat lembur 30 menit.
             *
             * Ini tetap dipertahankan seperti Seeder lama.
             */
            $isOvertime = $index % 3 === 0;

            $overtimeMinutes = $isOvertime
                ? 30
                : 0;

            $checkOutAt = $scheduledEnd
                ->copy()
                ->addMinutes($overtimeMinutes);

            /**
             * =====================================================
             * BONUS DATANG AWAL
             * =====================================================
             *
             * Bonus hanya true kalau benar-benar datang
             * sebelum mulai_bonus_datang.
             *
             * Untuk Admin 1 yang telat:
             *
             * bonus = false
             *
             * Untuk employee lain yang datang lebih awal:
             *
             * bonus = true
             */

            $bonusDidapat = false;

            if ($shift->mulai_bonus_datang) {

                $bonusStart = Carbon::parse(
                    $workDate . ' ' . $shift->mulai_bonus_datang,
                    $timezone
                );

                $bonusDidapat = $checkInAt->lessThan($bonusStart);
            }

            /**
             * =====================================================
             * SIMPAN ATTENDANCE
             * =====================================================
             */
            Attendance::updateOrCreate(
                [
                    'employee_shift_schedule_id' =>
                        $schedule->id,
                ],
                [
                    'employee_id' =>
                        $schedule->employee_id,

                    'employee_shift_schedule_id' =>
                        $schedule->id,

                    'attendance_date' =>
                        $workDate,

                    /**
                     * CHECK-IN AKTUAL
                     */
                    'check_in' =>
                        $checkInAt->format('H:i:s'),

                    /**
                     * CHECK-OUT AKTUAL
                     */
                    'check_out' =>
                        $checkOutAt->format('H:i:s'),

                    /**
                     * JADWAL SHIFT
                     */
                    'scheduled_check_in' =>
                        $scheduledStart->format('H:i:s'),

                    'scheduled_check_out' =>
                        $scheduledEnd->format('H:i:s'),

                    /**
                     * TOLERANSI
                     */
                    'late_tolerance_minutes' =>
                        $tolerance,

                    /**
                     * BATAS TELAT
                     */
                    'check_in_limit' =>
                        $lateLimit->format('H:i:s'),

                    /**
                     * STATUS
                     */
                    'status' =>
                        $status,

                    /**
                     * METODE ABSENSI
                     */
                    'metode' =>
                        $index % 2 === 0
                            ? 'selfie'
                            : 'qr',

                    /**
                     * BONUS DATANG AWAL
                     */
                    'bonus_didapat' =>
                        $bonusDidapat,
                ]
            );

            /**
             * =====================================================
             * STATISTIK
             * =====================================================
             */
            $totalAttendance++;

            if ($actualLateMinutes > 0) {

                $totalTerlambat++;

                if ($isAdmin1) {
                    $admin1Terlambat++;
                }

            } else {

                $totalTepatWaktu++;
            }

            if ($bonusDidapat) {
                $totalBonusAwal++;
            }

            if ($isOvertime) {
                $totalLembur++;
            }

            /**
             * =====================================================
             * LOG
             * =====================================================
             */
            $this->command?->line(
                sprintf(
                    'Employee %s (%s) | %s | Shift %s-%s | Check In %s | %s | Telat Efektif %d menit | Check Out %s | Lembur %d menit | Bonus Awal %s',
                    $schedule->employee_id,

                    $employee->name,

                    $workDate,

                    $scheduledStart->format('H:i'),

                    $scheduledEnd->format('H:i'),

                    $checkInAt->format('H:i'),

                    strtoupper($status),

                    $actualLateMinutes,

                    $checkOutAt->format('H:i'),

                    $overtimeMinutes,

                    $bonusDidapat ? 'YA' : 'TIDAK'
                )
            );
        }

        /**
         * =========================================================
         * HASIL SEEDER
         * =========================================================
         */
        $this->command?->newLine();

        $this->command?->info(
            '=============================================='
        );

        $this->command?->info(
            'ATTENDANCE SEED SELESAI'
        );

        $this->command?->info(
            '=============================================='
        );

        $this->command?->info(
            'Periode             : ' .
            $periodStart->format('d-m-Y') .
            ' s/d ' .
            $periodEnd->format('d-m-Y')
        );

        $this->command?->info(
            'Total Attendance    : ' .
            $totalAttendance
        );

        $this->command?->info(
            'Hadir / Tepat Waktu : ' .
            $totalTepatWaktu
        );

        $this->command?->info(
            'Total Terlambat     : ' .
            $totalTerlambat
        );

        $this->command?->info(
            'Terlambat Admin 1   : ' .
            $admin1Terlambat
        );

        $this->command?->info(
            'Bonus Datang Awal   : ' .
            $totalBonusAwal
        );

        $this->command?->info(
            'Attendance Lembur   : ' .
            $totalLembur
        );

        $this->command?->newLine();

        $this->command?->info(
            'ADMIN 1 dibuat memiliki beberapa keterlambatan.'
        );

        $this->command?->info(
            'EMPLOYEE LAIN tetap datang lebih awal.'
        );

        $this->command?->info(
            'Data siap digunakan untuk demo payroll.'
        );

        $this->command?->newLine();
    }
}

