<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeShiftSchedule;
use Carbon\Carbon;

class AttendanceShiftService
{
    public function resolveForCheckIn(Employee $employee, ?Carbon $now = null): ?EmployeeShiftSchedule
    {
        $now ??= Carbon::now('Asia/Jakarta');

        $previous = EmployeeShiftSchedule::with('shift')
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', $now->copy()->subDay()->toDateString())
            ->where('status', 'work')
            ->first();

        if ($previous?->shift?->lintas_hari) {
            $window = $this->buildShiftWindow($previous);
            $alreadyRecorded = Attendance::where('employee_shift_schedule_id', $previous->id)->exists();

            // Shift lintas hari kemarin hanya menjadi kandidat Check In bila
            // waktunya memang masih berada di dalam window shift tersebut dan
            // belum pernah memiliki attendance. Sesi yang sudah pernah dicatat
            // tidak boleh "mengunci" employee dari jadwal hari ini.
            if (
                !$alreadyRecorded
                && $now->betweenIncluded($window['start'], $window['end'])
            ) {
                return $previous;
            }
        }

        return EmployeeShiftSchedule::with('shift')
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', $now->toDateString())
            ->first();
    }

    public function resolveToday(Employee $employee, ?Carbon $now = null): ?EmployeeShiftSchedule
    {
        $now ??= Carbon::now('Asia/Jakarta');

        return EmployeeShiftSchedule::with('shift')
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', $now->toDateString())
            ->first();
    }

    public function findOpenAttendance(Employee $employee): ?Attendance
    {
        return Attendance::with('shiftSchedule.shift')
            ->where('employee_id', $employee->id)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->latest('attendance_date')
            ->latest('id')
            ->first();
    }

    public function buildShiftWindow(EmployeeShiftSchedule $schedule): array
    {
        $schedule->loadMissing('shift');
        $shift = $schedule->shift;

        if (!$shift) {
            throw new \RuntimeException('Work shift tidak ditemukan untuk jadwal ini.');
        }

        $workDate = Carbon::parse($schedule->work_date, 'Asia/Jakarta')->format('Y-m-d');

        $start = Carbon::parse($workDate . ' ' . $shift->jam_masuk, 'Asia/Jakarta');
        $end = Carbon::parse($workDate . ' ' . $shift->jam_pulang, 'Asia/Jakarta');

        if ($shift->lintas_hari || $end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $lateLimit = $start->copy()->addMinutes($shift->late_tolerance_minutes ?? 10);

        return [
            'start' => $start,
            'late_limit' => $lateLimit,
            'end' => $end,
        ];
    }

    public function attendanceStatus(EmployeeShiftSchedule $schedule, Carbon $checkInAt): string
    {
        $window = $this->buildShiftWindow($schedule);

        return $checkInAt->greaterThan($window['late_limit'])
            ? 'terlambat'
            : 'hadir';
    }

    public function qualifiesForEarlyBonus(EmployeeShiftSchedule $schedule, Carbon $checkInAt): bool
    {
        $schedule->loadMissing('shift');
        $shift = $schedule->shift;

        if (!$shift?->mulai_bonus_datang) {
            return false;
        }

        $workDate = Carbon::parse($schedule->work_date, 'Asia/Jakarta')->format('Y-m-d');
        $bonusLimit = Carbon::parse($workDate . ' ' . $shift->mulai_bonus_datang, 'Asia/Jakarta');

        return $checkInAt->lessThanOrEqualTo($bonusLimit);
    }
}
