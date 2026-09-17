<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use Illuminate\Http\JsonResponse;

class CashAdvanceController extends Controller
{
    /**
     * Menampilkan seluruh kasbon yang sudah di-approve admin.
     */
    public function index(): JsonResponse
    {
        $cashAdvances = CashAdvance::with('employee')
            ->where('status', 'approved')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data kasbon.',
            'data' => $cashAdvances,
        ]);
    }

    /**
     * Detail kasbon.
     */
    public function show($id): JsonResponse
    {
        $cashAdvance = CashAdvance::with('employee')->find($id);

        if (!$cashAdvance) {
            return response()->json([
                'success' => false,
                'message' => 'Kasbon tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail kasbon.',
            'data' => $cashAdvance,
        ]);
    }

    /**
     * Finance mencairkan kasbon.
     */
    public function pay($id): JsonResponse
    {
        $cashAdvance = CashAdvance::find($id);

        if (!$cashAdvance) {
            return response()->json([
                'success' => false,
                'message' => 'Kasbon tidak ditemukan.',
            ], 404);
        }

        if ($cashAdvance->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Kasbon belum disetujui admin.',
            ], 400);
        }

        if ($cashAdvance->is_paid) {
            return response()->json([
                'success' => false,
                'message' => 'Kasbon sudah dicairkan.',
            ], 400);
        }

        $cashAdvance->update([
            'is_paid' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kasbon berhasil dicairkan.',
            'data' => $cashAdvance->fresh(),
        ]);
    }
}