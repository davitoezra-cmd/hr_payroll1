<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('attendances')
            ->whereNull('employee_shift_schedule_id')
            ->orderBy('id')
            ->chunkById(200, function ($attendances) {
                foreach ($attendances as $attendance) {
                    $schedule = DB::table('employee_shift_schedules as schedules')
                        ->join('work_shifts as shifts', 'shifts.id', '=', 'schedules.shift_id')
                        ->where('schedules.employee_id', $attendance->employee_id)
                        ->whereDate('schedules.work_date', $attendance->attendance_date)
                        ->where('schedules.status', 'work')
                        ->select([
                            'schedules.id as schedule_id',
                            'shifts.jam_masuk',
                            'shifts.jam_pulang',
                            'shifts.batas_telat',
                            'shifts.late_tolerance_minutes',
                        ])
                        ->first();

                    if (!$schedule) {
                        continue;
                    }

                    DB::table('attendances')
                        ->where('id', $attendance->id)
                        ->update([
                            'employee_shift_schedule_id' => $schedule->schedule_id,
                            'scheduled_check_in' => $schedule->jam_masuk,
                            'scheduled_check_out' => $schedule->jam_pulang,
                            'late_tolerance_minutes' => $schedule->late_tolerance_minutes,
                            'check_in_limit' => $schedule->batas_telat,
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Backfill only associates historical rows; schema rollback is handled by the previous migration.
    }
};
