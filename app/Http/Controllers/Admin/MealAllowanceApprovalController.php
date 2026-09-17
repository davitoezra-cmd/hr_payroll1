<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MealAllowanceRequest;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MealAllowanceApprovalController extends Controller
{
    /**
     * Semua pengajuan uang makan.
     */
    public function index()
    {
        $mealAllowances = MealAllowanceRequest::with('employee')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $mealAllowances
        ]);
    }

    /**
     * Detail pengajuan uang makan.
     */
    public function show($id)
    {
        $mealAllowance = MealAllowanceRequest::with('employee')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $mealAllowance
        ]);
    }

    /**
     * Approve pengajuan uang makan.
     */
    public function approve(Request $request, $id)
    {
        $mealAllowance = MealAllowanceRequest::with('employee')
            ->findOrFail($id);

        if ($mealAllowance->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan sudah diproses.'
            ], 400);
        }

        $mealAllowance->update([
            'status' => 'approved',
            'approved_by' => Auth::user()->id,
            'approved_at' => now(),
        ]);

        // ==========================================
        // KIRIM NOTIFIKASI WHATSAPP
        // ==========================================
        try {
            $employee = $mealAllowance->employee;

            if ($employee && !empty($employee->phone)) {

                $amount = number_format(
                    $mealAllowance->amount,
                    0,
                    ',',
                    '.'
                );

                $message =
                    "Halo {$employee->name},\n\n" .
                    "Pengajuan uang makan Anda telah *DISETUJUI*.\n\n" .
                    "Tanggal uang makan: {$mealAllowance->meal_date}\n" .
                    "Nominal: Rp {$amount}\n\n" .
                    "Silakan cek aplikasi untuk melihat detail pengajuan.\n\n" .
                    "Terima kasih.";

                app(WhatsAppService::class)->send(
                    $employee->phone,
                    $message
                );
            }
        } catch (\Throwable $e) {

            // Jangan gagalkan approval jika WhatsApp gagal
            logger()->error(
                '[WHATSAPP] Gagal mengirim notifikasi approval uang makan',
                [
                    'meal_allowance_id' => $mealAllowance->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan uang makan berhasil disetujui.',
            'data' => $mealAllowance->fresh('employee')
        ]);
    }

    /**
     * Reject pengajuan uang makan.
     */
    public function reject(Request $request, $id)
    {
        $mealAllowance = MealAllowanceRequest::with('employee')
            ->findOrFail($id);

        if ($mealAllowance->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan sudah diproses.'
            ], 400);
        }

        $mealAllowance->update([
            'status' => 'rejected',
            'approved_by' => Auth::user()->id,
            'approved_at' => now(),
        ]);

        // ==========================================
        // KIRIM NOTIFIKASI WHATSAPP
        // ==========================================
        try {
            $employee = $mealAllowance->employee;

            if ($employee && !empty($employee->phone)) {

                $amount = number_format(
                    $mealAllowance->amount,
                    0,
                    ',',
                    '.'
                );

                $message =
                    "Halo {$employee->name},\n\n" .
                    "Pengajuan uang makan Anda telah *DITOLAK*.\n\n" .
                    "Tanggal uang makan: {$mealAllowance->meal_date}\n" .
                    "Nominal: Rp {$amount}\n\n" .
                    "Silakan cek aplikasi untuk melihat detail pengajuan.\n\n" .
                    "Terima kasih.";

                app(WhatsAppService::class)->send(
                    $employee->phone,
                    $message
                );
            }
        } catch (\Throwable $e) {

            // Jangan gagalkan reject jika WhatsApp gagal
            logger()->error(
                '[WHATSAPP] Gagal mengirim notifikasi rejection uang makan',
                [
                    'meal_allowance_id' => $mealAllowance->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan uang makan berhasil ditolak.',
            'data' => $mealAllowance->fresh('employee')
        ]);
    }
}