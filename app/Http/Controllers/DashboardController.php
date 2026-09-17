<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use App\Models\Employee;

class DashboardController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Dashboard Super Admin
    |--------------------------------------------------------------------------
    */

   public function superAdmin(): JsonResponse
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User belum login'
        ], 401);
    }

    /*
    |--------------------------------------------------------------------------
    | Periode dashboard
    |--------------------------------------------------------------------------
    */
    $year = now()->year;
    $month = now()->month;

    /*
    |--------------------------------------------------------------------------
    | Data absensi bulan berjalan
    |--------------------------------------------------------------------------
    */
    $attendances = Attendance::whereYear('attendance_date', $year)
        ->whereMonth('attendance_date', $month)
        ->orderBy('attendance_date')
        ->get([
            'id',
            'employee_id',
            'attendance_date',
            'check_in',
            'check_out',
            'status',
            'bonus_didapat',
        ]);

    /*
    |--------------------------------------------------------------------------
    | Statistik absensi
    |--------------------------------------------------------------------------
    */
    $totalAttendance = $attendances
        ->whereIn('status', ['hadir', 'terlambat'])
        ->count();

    $hadir = $attendances
        ->where('status', 'hadir')
        ->count();

    $terlambat = $attendances
        ->where('status', 'terlambat')
        ->count();

    $izin = $attendances
        ->where('status', 'izin')
        ->count();

    $absen = $attendances
        ->where('status', 'absen')
        ->count();

    /*
    |--------------------------------------------------------------------------
    | Grafik absensi harian
    |--------------------------------------------------------------------------
    |
    | Contoh:
    | tanggal 1:
    | hadir = 3
    | terlambat = 1
    |
    */
    $daysInMonth = Carbon::create(
        $year,
        $month,
        1
    )->daysInMonth;

    $dailyAttendance = [];

    for ($day = 1; $day <= $daysInMonth; $day++) {

        $date = Carbon::create(
            $year,
            $month,
            $day
        )->toDateString();

        $dayAttendances = $attendances->filter(function ($attendance) use ($date) {
            return Carbon::parse($attendance->attendance_date)
                ->toDateString() === $date;
        });

        $dailyAttendance[] = [
            'date' => $date,
            'day' => $day,
            'hadir' => $dayAttendances
                ->where('status', 'hadir')
                ->count(),

            'terlambat' => $dayAttendances
                ->where('status', 'terlambat')
                ->count(),

            'izin' => $dayAttendances
                ->where('status', 'izin')
                ->count(),

            'absen' => $dayAttendances
                ->where('status', 'absen')
                ->count(),

            'total' => $dayAttendances
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count(),
        ];
    }

    return response()->json([
        'success' => true,

        'message' => 'Dashboard Super Admin',

        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],

        /*
        |--------------------------------------------------------------------------
        | Statistik absensi
        |--------------------------------------------------------------------------
        */
        'attendance_statistics' => [
            'year' => $year,
            'month' => $month,
            'total' => $totalAttendance,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => $izin,
            'absen' => $absen,
        ],

        /*
        |--------------------------------------------------------------------------
        | Grafik absensi harian
        |--------------------------------------------------------------------------
        */
        'attendance_chart' => $dailyAttendance,

        /*
        |--------------------------------------------------------------------------
        | Data absensi mentah jika nanti diperlukan
        |--------------------------------------------------------------------------
        */
        'attendances' => $attendances,
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | Dashboard Employee
    |--------------------------------------------------------------------------
    */

    public function employee(): JsonResponse
    {

       $employee = auth()->user();


        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee belum login'
            ], 401);
        }


        $today = now()->toDateString();


        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();



        $presentThisMonth = Attendance::where('employee_id', $employee->id)
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->whereIn('status', [
                'hadir',
                'terlambat'
            ])
            ->count();



        $lateThisMonth = Attendance::where('employee_id', $employee->id)
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->where('status', 'terlambat')
            ->count();



        $absentThisMonth = Attendance::where('employee_id', $employee->id)
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->where('status', 'absen')
            ->count();



        $workingHours = '-';


        if (
            $todayAttendance &&
            $todayAttendance->check_in &&
            $todayAttendance->check_out
        ) {


            $checkIn = Carbon::createFromTimeString(
                $todayAttendance->check_in
            );


            $checkOut = Carbon::createFromTimeString(
                $todayAttendance->check_out
            );


            $workingHours = $checkIn
                ->diff($checkOut)
                ->format('%H Jam %I Menit');

        }



        $activities = Attendance::where('employee_id', $employee->id)
            ->latest('attendance_date')
            ->take(5)
            ->get([
                'attendance_date',
                'check_in',
                'check_out',
                'status'
            ]);



        return response()->json([

            'success' => true,


            'user' => [

                'id' => $employee->id,

                'name' => $employee->name,

                'email' => $employee->email,

            ],


            'today' => [

                'status' => $todayAttendance?->status,

                'check_in' => $todayAttendance?->check_in,

                'check_out' => $todayAttendance?->check_out,

            ],


            'statistics' => [

                'present_this_month' => $presentThisMonth,

                'late_this_month' => $lateThisMonth,

                'absent_this_month' => $absentThisMonth,


            ],


            'recent_activities' => $activities,

        ]);

    }
    /*
|--------------------------------------------------------------------------
| Dashboard Finance
|--------------------------------------------------------------------------
*/

public function finance(): \Illuminate\Http\JsonResponse
{
    $finance = auth()->user();

    if (!$finance) {
        return response()->json([
            'success' => false,
            'message' => 'Finance belum login'
        ], 401);
    }

    return response()->json([
        'success' => true,
        'message' => 'Dashboard Finance',
        'user' => [
            'id' => $finance->id,
            'name' => $finance->name,
            'email' => $finance->email,
        ],
    ]);
}

    public function supervisor(): \Illuminate\Http\JsonResponse
{
    $supervisor = auth()->user();

    if (!$supervisor) {
        return response()->json([
            'success' => false,
            'message' => 'Supervisor belum login'
        ], 401);
    }

    $today = now()->toDateString();

    // Total seluruh karyawan
    $totalEmployee = Employee::count();

    // Statistik kehadiran hari ini
    $hadir = Attendance::whereDate('attendance_date', $today)
        ->where('status', 'hadir')
        ->count();

    $terlambat = Attendance::whereDate('attendance_date', $today)
        ->where('status', 'terlambat')
        ->count();

    $izin = Attendance::whereDate('attendance_date', $today)
        ->where('status', 'izin')
        ->count();

    $absen = Attendance::whereDate('attendance_date', $today)
        ->where('status', 'absen')
        ->count();

    // Daftar kehadiran hari ini
    $todayAttendance = Attendance::with('employee')
        ->whereDate('attendance_date', $today)
        ->orderBy('check_in')
        ->get();

    return response()->json([
        'success' => true,

        'user' => [
            'id' => $supervisor->id,
            'name' => $supervisor->name,
            'email' => $supervisor->email,
        ],

        'statistics' => [
            'total_employee' => $totalEmployee,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => $izin,
            'absen' => $absen,
        ],

        'today_attendance' => $todayAttendance,
    ]);
}


}