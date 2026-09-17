<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\EmployeePerformance;
use Illuminate\Http\Request;

class EmployeePerformanceController extends Controller
{
    /**
     * Menampilkan seluruh hasil penilaian milik employee yang login.
     */
    public function index()
    {
        $employee = auth()->user();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee belum login.'
            ], 401);
        }

        $performances = EmployeePerformance::with([
                'employee',
                'employeeTarget',
                'supervisor'
            ])
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'count' => $performances->count(),
            'data' => $performances
        ]);
    }

    /**
     * Detail hasil penilaian.
     */
    public function show($id)
    {
        $employee = auth()->user();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee belum login.'
            ], 401);
        }

        $performance = EmployeePerformance::with([
                'employee',
                'employeeTarget',
                'supervisor'
            ])
            ->where('employee_id', $employee->id)
            ->find($id);

        if (!$performance) {
            return response()->json([
                'success' => false,
                'message' => 'Data penilaian tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $performance
        ]);
    }
}