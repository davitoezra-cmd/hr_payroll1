<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceQr;
use App\Models\Employee;
use App\Services\AttendanceShiftService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceQrController extends Controller
{
    public function __construct(private readonly AttendanceShiftService $shiftService)
    {
    }

    public function scan(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        /** @var Employee $employee */
        $employee = $request->user();

        $qr = AttendanceQr::where('token', $request->token)
            ->where('is_active', true)
            ->first();

        if (!$qr) {
            return response()->json([
                'success' => false,
                'message' => 'QR tidak valid.',
            ], 404);
        }

        if ($qr->expired_at && now()->greaterThan($qr->expired_at)) {
            return response()->json([
                'success' => false,
                'message' => 'QR sudah kedaluwarsa.',
            ], 410);
        }

        $openAttendance = $this->shiftService->findOpenAttendance($employee);

        if ($openAttendance) {
            $openAttendance->update([
                'check_out' => Carbon::now('Asia/Jakarta')->format('H:i:s'),
                'metode' => 'qr',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check Out berhasil.',
                'data' => $openAttendance->fresh()->load('shiftSchedule.shift'),
            ]);
        }

        $now = Carbon::now('Asia/Jakarta');
        $schedule = $this->shiftService->resolveForCheckIn($employee, $now);

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki jadwal kerja untuk sesi ini.',
            ], 422);
        }

        if ($schedule->status === 'off') {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal Anda berstatus OFF. Check In tidak tersedia.',
            ], 422);
        }

        if (!$schedule->shift || !$schedule->shift->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Work shift pada jadwal ini tidak aktif atau tidak ditemukan.',
            ], 422);
        }

        if (Attendance::where('employee_shift_schedule_id', $schedule->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Presensi untuk jadwal kerja ini sudah selesai.',
            ], 409);
        }

        $window = $this->shiftService->buildShiftWindow($schedule);
        $status = $this->shiftService->attendanceStatus($schedule, $now);
        $bonus = $this->shiftService->qualifiesForEarlyBonus($schedule, $now);

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'employee_shift_schedule_id' => $schedule->id,
            'attendance_date' => $schedule->work_date,
            'check_in' => $now->format('H:i:s'),
            'scheduled_check_in' => $window['start']->format('H:i:s'),
            'scheduled_check_out' => $window['end']->format('H:i:s'),
            'late_tolerance_minutes' => $schedule->shift->late_tolerance_minutes ?? 10,
            'check_in_limit' => $window['late_limit']->format('H:i:s'),
            'status' => $status,
            'metode' => 'qr',
            'bonus_didapat' => $bonus,
        ]);

        return response()->json([
            'success' => true,
            'message' => $status === 'terlambat'
                ? 'Check In berhasil, Anda tercatat terlambat.'
                : 'Check In berhasil.',
            'data' => $attendance->load('shiftSchedule.shift'),
        ], 201);
    }
}
