<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use App\Events\CashAdvanceStatusUpdated;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CashAdvanceApprovalController extends Controller
{
    /**
     * Daftar seluruh pengajuan kasbon.
     */
    public function index()
    {
        $cashAdvances = CashAdvance::with('employee')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cashAdvances
        ]);
    }

    /**
     * Detail pengajuan kasbon.
     */
    public function show($id)
    {
        $cashAdvance = CashAdvance::with('employee')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $cashAdvance
        ]);
    }

    /**
     * Approve pengajuan kasbon.
     */
    public function approve(
        Request $request,
        $id,
        WhatsAppService $whatsappService
    ) {
        $cashAdvance = CashAdvance::with('employee')
            ->findOrFail($id);

        if ($cashAdvance->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan sudah diproses.',
            ], 400);
        }

        $cashAdvance->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        /**
         * =====================================================
         * WHATSAPP NOTIFICATION
         * =====================================================
         */
        try {
            $employee = $cashAdvance->employee;
            $phone = $employee?->phone;

            if ($phone) {

                $nominal = number_format(
                    (float) $cashAdvance->amount,
                    0,
                    ',',
                    '.'
                );

                $message =
                    "Halo {$employee->name},\n\n" .
                    "Pengajuan Cash Advance Anda telah *disetujui*.\n\n" .
                    "Nominal: Rp{$nominal}\n" .
                    "Status: Disetujui\n\n" .
                    "Silakan cek aplikasi HR Payroll untuk detail pengajuan.\n\n" .
                    "Terima kasih.";

                $whatsappService->send(
                    $phone,
                    $message
                );
            }

        } catch (\Throwable $e) {

            // Jangan membuat approval gagal hanya karena WA error
            Log::error(
                'Gagal mengirim WhatsApp Cash Advance Approved',
                [
                    'cash_advance_id' => $cashAdvance->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        /**
         * =====================================================
         * EVENT STATUS CASH ADVANCE
         * =====================================================
         *
         * Tetap dipertahankan sesuai kode sebelumnya.
         */
        event(new CashAdvanceStatusUpdated($cashAdvance));

        return response()->json([
            'success' => true,
            'message' => 'Kasbon berhasil disetujui',
            'data' => $cashAdvance->fresh(),
        ]);
    }

    /**
     * Reject pengajuan kasbon.
     */
    public function reject(
        Request $request,
        $id,
        WhatsAppService $whatsappService
    ) {
        $cashAdvance = CashAdvance::with('employee')
            ->findOrFail($id);

        if ($cashAdvance->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan sudah diproses.'
            ], 400);
        }

        $cashAdvance->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        /**
         * =====================================================
         * WHATSAPP NOTIFICATION
         * =====================================================
         */
        try {
            $employee = $cashAdvance->employee;
            $phone = $employee?->phone;

            if ($phone) {

                $nominal = number_format(
                    (float) $cashAdvance->amount,
                    0,
                    ',',
                    '.'
                );

                $message =
                    "Halo {$employee->name},\n\n" .
                    "Pengajuan Cash Advance Anda telah *ditolak*.\n\n" .
                    "Nominal: Rp{$nominal}\n" .
                    "Status: Ditolak\n\n" .
                    "Silakan cek aplikasi HR Payroll untuk detail pengajuan.\n\n" .
                    "Terima kasih.";

                $whatsappService->send(
                    $phone,
                    $message
                );
            }

        } catch (\Throwable $e) {

            // WA error tidak membatalkan proses reject
            Log::error(
                'Gagal mengirim WhatsApp Cash Advance Rejected',
                [
                    'cash_advance_id' => $cashAdvance->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        /**
         * =====================================================
         * EVENT STATUS CASH ADVANCE
         * =====================================================
         *
         * Tetap dipertahankan sesuai kode sebelumnya.
         */
        event(new CashAdvanceStatusUpdated($cashAdvance));

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan kasbon berhasil ditolak.',
            'data' => $cashAdvance->fresh()
        ]);
    }
}