<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessTrip;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessTripApprovalController extends Controller
{
    /**
     * Semua pengajuan dinas luar.
     */
    public function index()
    {
        $businessTrips = BusinessTrip::with('employee')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $businessTrips
        ]);
    }

    /**
     * Detail pengajuan dinas luar.
     */
    public function show($id)
    {
        $businessTrip = BusinessTrip::with('employee')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $businessTrip
        ]);
    }

    /**
     * Approve pengajuan.
     */
    public function approve(Request $request, $id)
{
    $businessTrip = BusinessTrip::with('employee')->findOrFail($id);

    if ($businessTrip->status != 'pending') {
        return response()->json([
            'success' => false,
            'message' => 'Pengajuan sudah diproses.'
        ], 400);
    }

    $businessTrip->update([
        'status' => 'approved',
        'approved_by' => Auth::id(),
        'approved_at' => now(),
    ]);

    // ==========================================
    // NOTIFIKASI WHATSAPP
    // ==========================================
    try {
        $employee = $businessTrip->employee;

        if ($employee && !empty($employee->phone)) {

            $phone = preg_replace(
                '/[^0-9]/',
                '',
                $employee->phone
            );

            // 08123456789 -> 628123456789
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }

            $message =
                "Halo {$employee->name},\n\n" .
                "Pengajuan dinas luar Anda telah *DISETUJUI*.\n\n" .
                "Silakan cek aplikasi untuk melihat detail perjalanan dinas Anda.\n\n" .
                "Terima kasih.";

            app(WhatsAppService::class)->send(
                $phone,
                $message
            );
        }
    } catch (\Throwable $e) {

        // WhatsApp gagal tidak boleh menggagalkan approval
        logger()->error(
            '[WHATSAPP] Gagal mengirim notifikasi approval dinas luar',
            [
                'business_trip_id' => $businessTrip->id,
                'error' => $e->getMessage(),
            ]
        );
    }

    return response()->json([
        'success' => true,
        'message' => 'Pengajuan dinas luar berhasil disetujui.',
        'data' => $businessTrip->fresh('employee')
    ]);
}

    /**
     * Reject pengajuan.
     */
   /**
 * Reject pengajuan.
 */
public function reject(Request $request, $id)
{
    $businessTrip = BusinessTrip::with('employee')->findOrFail($id);

    if ($businessTrip->status != 'pending') {
        return response()->json([
            'success' => false,
            'message' => 'Pengajuan sudah diproses.'
        ], 400);
    }

    $businessTrip->update([
        'status' => 'rejected',
        'approved_by' => Auth::id(),
        'approved_at' => now(),
    ]);

    // ==========================================
    // NOTIFIKASI WHATSAPP
    // ==========================================
    try {
        $employee = $businessTrip->employee;

        if ($employee && !empty($employee->phone)) {

            $phone = preg_replace(
                '/[^0-9]/',
                '',
                $employee->phone
            );

            // 08123456789 -> 628123456789
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }

            $message =
                "Halo {$employee->name},\n\n" .
                "Pengajuan dinas luar Anda telah *DITOLAK*.\n\n" .
                "Silakan cek aplikasi untuk melihat detail pengajuan dan alasan penolakan jika tersedia.\n\n" .
                "Terima kasih.";

            app(WhatsAppService::class)->send(
                $phone,
                $message
            );
        }
    } catch (\Throwable $e) {

        // WhatsApp gagal tidak boleh menggagalkan rejection
        logger()->error(
            '[WHATSAPP] Gagal mengirim notifikasi rejection dinas luar',
            [
                'business_trip_id' => $businessTrip->id,
                'error' => $e->getMessage(),
            ]
        );
    }

    return response()->json([
        'success' => true,
        'message' => 'Pengajuan dinas luar berhasil ditolak.',
        'data' => $businessTrip->fresh('employee')
    ]);
}
}