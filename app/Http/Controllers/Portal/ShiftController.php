<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\AttendanceShiftService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function today(Request $request, AttendanceShiftService $shiftService)
    {
        /** @var Employee $employee */
        $employee = $request->user();
        $schedule = $shiftService->resolveToday($employee, Carbon::now('Asia/Jakarta'));

        if (!$schedule) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal hari ini belum tersedia.',
                'data' => null,
            ]);
        }

        $shift = $schedule->shift;
        $durationMinutes = null;
        $lateLimit = null;

        if ($schedule->status === 'work' && $shift) {
            $window = $shiftService->buildShiftWindow($schedule);
            $durationMinutes = $window['start']->diffInMinutes($window['end']);
            $lateLimit = $window['late_limit']->format('H:i:s');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $schedule->id,
                'employee' => [
                    'id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'name' => $employee->name,
                ],
                'work_date' => $schedule->work_date->format('Y-m-d'),
                'status' => $schedule->status,
                'duration_minutes' => $durationMinutes,
                'notes' => $schedule->notes,
                'shift' => $schedule->status === 'work' && $shift
                    ? [
                        'id' => $shift->id,
                        'code' => $shift->code,
                        'name' => $shift->name,
                        'jam_masuk' => $shift->jam_masuk,
                        'jam_pulang' => $shift->jam_pulang,
                        'late_tolerance_minutes' => $shift->late_tolerance_minutes ?? 10,
                        'batas_telat' => $lateLimit,
                        'lintas_hari' => $shift->lintas_hari,
                    ]
                    : null,
            ],
        ]);
    }
}
