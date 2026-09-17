<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\AttendanceLocation;
use App\Services\AttendanceShiftService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceShiftService $shiftService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var Employee $employee */
        $employee = $request->user();

        $attendance = $this->shiftService->findOpenAttendance($employee);

        if (!$attendance) {
            $attendance = Attendance::with('shiftSchedule.shift')
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', Carbon::today('Asia/Jakarta')->toDateString())
                ->latest('id')
                ->first();
        }

        return response()->json([
            'success' => true,
            'data' => $attendance,
        ]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        /** @var Employee $employee */
        $employee = $request->user();

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'image_selfie' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | CEK LOKASI ABSENSI
        |--------------------------------------------------------------------------
        | Mengambil lokasi absensi yang sedang aktif.
        | Hanya boleh ada satu lokasi aktif.
        */
        $attendanceLocation = AttendanceLocation::where('is_active', true)->first();

        if (!$attendanceLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi absensi belum diatur atau belum diaktifkan oleh Admin.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | HITUNG JARAK GPS KARYAWAN DENGAN TITIK LOKASI
        |--------------------------------------------------------------------------
        | Menggunakan rumus Haversine.
        | Hasil jarak dalam meter.
        */
        $earthRadius = 6371000;

        $latitudeEmployee = deg2rad((float) $request->latitude);
        $latitudeLocation = deg2rad((float) $attendanceLocation->latitude);

        $differenceLatitude = deg2rad(
            (float) $request->latitude - (float) $attendanceLocation->latitude
        );

        $differenceLongitude = deg2rad(
            (float) $request->longitude - (float) $attendanceLocation->longitude
        );

        $a = sin($differenceLatitude / 2) ** 2
            + cos($latitudeEmployee)
            * cos($latitudeLocation)
            * sin($differenceLongitude / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        /*
        |--------------------------------------------------------------------------
        | CEK APAKAH KARYAWAN MASIH DALAM RADIUS
        |--------------------------------------------------------------------------
        */
        if ($distance > $attendanceLocation->radius_meter) {
            return response()->json([
                'success' => false,
                'message' => 'Check In ditolak. Anda berada di luar area absensi.',
                'data' => [
                    'lokasi_absensi' => $attendanceLocation->name,
                    'jarak_meter' => round($distance),
                    'radius_maksimal' => $attendanceLocation->radius_meter,
                ],
            ], 422);
        }

        if ($this->shiftService->findOpenAttendance($employee)) {
            return response()->json([
                'success' => false,
                'message' => 'Masih ada sesi presensi yang belum Check Out.',
            ], 409);
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

        $alreadyExists = Attendance::where('employee_shift_schedule_id', $schedule->id)->exists();

        if ($alreadyExists) {
            return response()->json([
                'success' => false,
                'message' => 'Presensi untuk jadwal kerja ini sudah tercatat.',
            ], 409);
        }

        $window = $this->shiftService->buildShiftWindow($schedule);
        $status = $this->shiftService->attendanceStatus($schedule, $now);
        $bonus = $this->shiftService->qualifiesForEarlyBonus($schedule, $now);

        $imagePath = $request->file('image_selfie')->store('attendance-selfie', 'public');

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'employee_shift_schedule_id' => $schedule->id,
            'attendance_date' => $schedule->work_date,
            'image_selfie' => $imagePath,
            'check_in' => $now->format('H:i:s'),
            'scheduled_check_in' => $window['start']->format('H:i:s'),
            'scheduled_check_out' => $window['end']->format('H:i:s'),
            'late_tolerance_minutes' => $schedule->shift->late_tolerance_minutes ?? 10,
            'check_in_limit' => $window['late_limit']->format('H:i:s'),
            'status' => $status,
            'metode' => 'selfie',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
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

    public function checkOut(Request $request): JsonResponse
    {
        /** @var Employee $employee */
        $employee = $request->user();

        /*
        |--------------------------------------------------------------------------
        | VALIDASI CHECK OUT
        |--------------------------------------------------------------------------
        | Check Out sekarang wajib mengirim lokasi GPS.
        */
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'image_selfie' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $attendance = $this->shiftService->findOpenAttendance($employee);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada sesi Check In yang masih terbuka.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | CEK LOKASI ABSENSI
        |--------------------------------------------------------------------------
        | Check Out juga harus berada di dalam radius lokasi absensi aktif.
        */
        $attendanceLocation = AttendanceLocation::where('is_active', true)->first();

        if (!$attendanceLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi absensi belum diatur atau belum diaktifkan oleh Admin.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | HITUNG JARAK GPS KARYAWAN DENGAN TITIK LOKASI
        |--------------------------------------------------------------------------
        */
        $earthRadius = 6371000;

        $latitudeEmployee = deg2rad((float) $request->latitude);
        $latitudeLocation = deg2rad((float) $attendanceLocation->latitude);

        $differenceLatitude = deg2rad(
            (float) $request->latitude - (float) $attendanceLocation->latitude
        );

        $differenceLongitude = deg2rad(
            (float) $request->longitude - (float) $attendanceLocation->longitude
        );

        $a = sin($differenceLatitude / 2) ** 2
            + cos($latitudeEmployee)
            * cos($latitudeLocation)
            * sin($differenceLongitude / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        /*
        |--------------------------------------------------------------------------
        | TOLAK CHECK OUT JIKA DI LUAR RADIUS
        |--------------------------------------------------------------------------
        */
        if ($distance > $attendanceLocation->radius_meter) {
            return response()->json([
                'success' => false,
                'message' => 'Check Out ditolak. Anda berada di luar area absensi.',
                'data' => [
                    'lokasi_absensi' => $attendanceLocation->name,
                    'jarak_meter' => round($distance),
                    'radius_maksimal' => $attendanceLocation->radius_meter,
                ],
            ], 422);
        }

        $now = Carbon::now('Asia/Jakarta');

        $attendance->update([
            'check_out' => $now->format('H:i:s'),
            'checkout_latitude' => $request->latitude,
            'checkout_longitude' => $request->longitude,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check Out berhasil.',
            'data' => $attendance->fresh()->load('shiftSchedule.shift'),
        ]);
    }
}
