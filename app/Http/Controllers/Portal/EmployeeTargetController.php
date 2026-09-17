<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\EmployeeTarget;
use Illuminate\Http\Request;

class EmployeeTargetController extends Controller
{
    /**
     * Menampilkan seluruh target milik employee yang login.
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

        $targets = EmployeeTarget::with('supervisor')
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'count' => $targets->count(),
            'data' => $targets
        ]);
    }

    /**
     * Detail target.
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

        $target = EmployeeTarget::with('supervisor')
            ->where('employee_id', $employee->id)
            ->find($id);

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'Target tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $target
        ]);
    }

    /**
     * Employee mengupdate progress target.
     */
    public function updateProgress(Request $request, $id)
    {
        $employee = auth()->user();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee belum login.'
            ], 401);
        }

        $request->validate([
            'current_value' => 'required|integer|min:0',
        ]);

        $target = EmployeeTarget::where('employee_id', $employee->id)
            ->find($id);

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'Target tidak ditemukan.'
            ], 404);
        }

        $target->current_value = $request->current_value;

        // Hitung progress otomatis
        if ($target->target_value > 0) {
            $target->progress_percent = round(
                ($target->current_value / $target->target_value) * 100,
                2
            );
        } else {
            $target->progress_percent = 0;
        }

        // Tentukan status otomatis
        if ($target->current_value >= $target->target_value) {
            $target->status = 'completed';
        } else {
            $target->status = 'ongoing';
        }

        $target->save();

        return response()->json([
            'success' => true,
            'message' => 'Progress target berhasil diperbarui.',
            'data' => $target
        ]);
    }
}