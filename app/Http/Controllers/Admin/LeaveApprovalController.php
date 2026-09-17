<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class LeaveApprovalController extends Controller
{
    /**
     * Menampilkan semua pengajuan cuti.
     */
    public function index()
    {
        $leaveRequests = LeaveRequest::with('employee')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $leaveRequests
        ]);
    }

    /**
     * Detail pengajuan.
     */
    public function show($id)
    {
        $leave = LeaveRequest::with('employee')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $leave
        ]);
    }

    /**
     * Approve pengajuan.
     */
    public function approve(
        Request $request,
        $id,
        WhatsAppService $whatsappService
    ) {
        $admin = $request->user();

        $leave = LeaveRequest::with('employee')
            ->findOrFail($id);

        if ($leave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan sudah diproses.'
            ], 400);
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        /**
         * =====================================================
         * KIRIM NOTIFIKASI WHATSAPP
         * =====================================================
         *
         * Nomor WhatsApp tidak perlu dinormalisasi di controller.
         * WhatsAppService akan menangani normalisasi nomor dan
         * memilih gateway/provider yang aktif.
         */
        try {
            $phone = $leave->employee?->phone;

            if ($phone) {
                $text =
                    "📢 *Pengajuan Cuti/Izin Disetujui*\n\n" .
                    "Halo *" . ($leave->employee->name ?? 'Employee') . ",\n\n" .
                    "Pengajuan Anda telah *disetujui*.\n\n" .
                    "Status: ✅ Disetujui\n" .
                    "Tanggal: " . $leave->start_date . " s/d " . $leave->end_date . "\n\n" .
                    "Silakan cek aplikasi untuk informasi lebih lengkap.\n\n" .
                    "Terima kasih.";

                $whatsappService->send(
                    $phone,
                    $text
                );
            }

        } catch (\Throwable $e) {

            // Jangan menggagalkan approval hanya karena WA gagal
            logger()->error(
                '[WHATSAPP] Gagal mengirim notifikasi approval cuti',
                [
                    'leave_id' => $leave->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil disetujui.',
            'data' => $leave->fresh()
        ]);
    }

    /**
     * Reject pengajuan.
     */
    public function reject(
        Request $request,
        $id,
        WhatsAppService $whatsappService
    ) {
        $admin = $request->user();

        $leave = LeaveRequest::with('employee')
            ->findOrFail($id);

        if ($leave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan sudah diproses.'
            ], 400);
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        /**
         * =====================================================
         * KIRIM NOTIFIKASI WHATSAPP
         * =====================================================
         *
         * Nomor WhatsApp tidak perlu dinormalisasi di controller.
         * WhatsAppService akan menangani normalisasi nomor dan
         * memilih gateway/provider yang aktif.
         */
        try {
            $phone = $leave->employee?->phone;

            if ($phone) {
                $text =
                    "📢 *Pengajuan Cuti/Izin Ditolak*\n\n" .
                    "Halo *" . ($leave->employee->name ?? 'Employee') . ",\n\n" .
                    "Mohon maaf, pengajuan Anda telah *ditolak*.\n\n" .
                    "Status: ❌ Ditolak\n" .
                    "Tanggal: " . $leave->start_date . " s/d " . $leave->end_date . "\n\n" .
                    "Silakan cek aplikasi untuk informasi lebih lengkap.\n\n" .
                    "Terima kasih.";

                $whatsappService->send(
                    $phone,
                    $text
                );
            }

        } catch (\Throwable $e) {

            // WA gagal tidak boleh membuat proses reject gagal
            logger()->error(
                '[WHATSAPP] Gagal mengirim notifikasi reject cuti',
                [
                    'leave_id' => $leave->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil ditolak.',
            'data' => $leave->fresh()
        ]);
    }
}