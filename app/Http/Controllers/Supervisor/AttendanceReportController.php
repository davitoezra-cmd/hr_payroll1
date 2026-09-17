<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
{
    $query = Attendance::with('employee');

    if ($request->filled('date')) {
        $query->whereDate('attendance_date', $request->date);
    }

    if ($request->filled('employee_id')) {
        $query->where('employee_id', $request->employee_id);
    }

    $attendance = $query->orderBy('attendance_date', 'desc')->get();

    return response()->json([
        'success' => true,
        'count' => $attendance->count(),
        'data' => $attendance,
    ]);
}
}