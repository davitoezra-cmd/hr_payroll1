<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalLeave;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalLeaveApprovalController extends Controller
{
    /**
     * Semua pengajuan sakit.
     */
    public function index()
    {
        $medicalLeaves = MedicalLeave::with('employee')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $medicalLeaves
        ]);
    }

    /**
     * Detail pengajuan sakit.
     */
    public function show($id)
    {
        $medicalLeave = MedicalLeave::with('employee')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $medicalLeave
        ]);
    }

    /**
     * Approve pengajuan sakit.
     */
    public function approve(Request $request, $id)
    {
        $medicalLeave = MedicalLeave::with('employee')
            ->findOrFail($id);

        if ($medicalLeave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan sudah diproses.'
            ], 400);
        }

        $medicalLeave->update([
            'status' => 'approved',
            'approved_by' => Auth::user()->id,
            'approved_at' => now(),
        ]);

        /**
         * =====================================================
         * KIRIM NOTIFIKASI WHATSAPP
         * =====================================================
         */
        try {
            $employee = $medicalLeave->employee;
            $phone = $employee?->phone;

            if ($phone) {
                $text =
                    "📢 *Pengajuan Sakit Disetujui*\n\n" .
                    "Halo *" . ($employee->name ?? 'Employee') . ",\n\n" .
                    "Pengajuan sakit Anda telah *disetujui*.\n\n" .
                    "Status: ✅ Disetujui\n\n" .
                    "Silakan cek aplikasi untuk informasi lebih lengkap.\n\n" .
                    "Terima kasih.";

                app(WhatsAppService::class)->send(
                    $phone,
                    $text
                );
            }

        } catch (\Throwable $e) {

            // Jangan membuat approval gagal jika WA error
            logger()->error(
                '[WHATSAPP] Gagal mengirim notifikasi approve medical leave',
                [
                    'medical_leave_id' => $medicalLeave->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan sakit berhasil disetujui.',
            'data' => $medicalLeave->fresh()
        ]);
    }

    /**
     * Reject pengajuan sakit.
     */
    public function reject(Request $request, $id)
    {
        $medicalLeave = MedicalLeave::with('employee')
            ->findOrFail($id);

        if ($medicalLeave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan sudah diproses.'
            ], 400);
        }

        $medicalLeave->update([
            'status' => 'rejected',
            'approved_by' => Auth::user()->id,
            'approved_at' => now(),
            'approval_note' => $request->approval_note,
        ]);

        /**
         * =====================================================
         * KIRIM NOTIFIKASI WHATSAPP
         * =====================================================
         */
        try {
            $employee = $medicalLeave->employee;
            $phone = $employee?->phone;

            if ($phone) {
                $approvalNote = $medicalLeave->approval_note
                    ? "\nAlasan: " . $medicalLeave->approval_note
                    : "";

                $text =
                    "📢 *Pengajuan Sakit Ditolak*\n\n" .
                    "Halo *" . ($employee->name ?? 'Employee') . ",\n\n" .
                    "Mohon maaf, pengajuan sakit Anda telah *ditolak*.\n\n" .
                    "Status: ❌ Ditolak" .
                    $approvalNote .
                    "\n\nSilakan cek aplikasi untuk informasi lebih lengkap.\n\n" .
                    "Terima kasih.";

                app(WhatsAppService::class)->send(
                    $phone,
                    $text
                );
            }

        } catch (\Throwable $e) {

            // WA gagal tidak membuat proses reject gagal
            logger()->error(
                '[WHATSAPP] Gagal mengirim notifikasi reject medical leave',
                [
                    'medical_leave_id' => $medicalLeave->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan sakit berhasil ditolak.',
            'data' => $medicalLeave->fresh()
        ]);
    }
}