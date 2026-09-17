<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CashAdvanceController extends Controller
{
    /**
     * Daftar pengajuan kasbon milik employee.
     */
    public function index()
    {
        $employee = Auth::user();

        $cashAdvances = CashAdvance::where('employee_id', $employee->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cashAdvances
        ]);
    }

    /**
     * Simpan pengajuan kasbon.
     */
    public function store(
        Request $request,
        WhatsAppService $whatsappService
    ) {
        $employee = Auth::user();

        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'reason' => 'required|string|max:1000',
        ]);

        $cashAdvance = CashAdvance::create([
            'employee_id' => $employee->id,
            'amount' => $request->amount,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        /*
        |--------------------------------------------------------------------------
        | KIRIM WHATSAPP KE ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = User::find(1);

        $adminPhone = $admin?->phone;

        $formattedAmount = 'Rp. ' . number_format(
            $cashAdvance->amount,
            0,
            ',',
            '.'
        ) . ',-';

        $approvalUrl = 'https://yukabsen.com/approval';

        $message =
            "{$employee->name} telah mengajukan untuk kasbon sebesar {$formattedAmount}.\n\n" .
            "Alasan: {$cashAdvance->reason}\n\n" .
            "Silahkan melakukan approval dengan mengklik link berikut:\n" .
            $approvalUrl;

        try {
            if ($adminPhone) {
                $whatsappService->send(
                    $adminPhone,
                    $message
                );
            }
        } catch (\Throwable $e) {
            // Gagal WhatsApp tidak boleh menggagalkan pengajuan kasbon
            Log::error(
                'Gagal mengirim WhatsApp pengajuan kasbon',
                [
                    'cash_advance_id' => $cashAdvance->id,
                    'employee_id' => $employee->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan kasbon berhasil dikirim.',
            'data' => $cashAdvance
        ], 201);
    }

    /**
     * Detail pengajuan.
     */
    public function show($id)
    {
        $employee = Auth::user();

        $cashAdvance = CashAdvance::where('employee_id', $employee->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $cashAdvance
        ]);
    }

    /**
     * Hapus jika masih pending.
     */
    public function destroy($id)
    {
        $employee = Auth::user();

        $cashAdvance = CashAdvance::where('employee_id', $employee->id)
            ->findOrFail($id);

        if ($cashAdvance->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan yang sudah diproses tidak dapat dihapus.'
            ], 400);
        }

        $cashAdvance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan kasbon berhasil dihapus.'
        ]);
    }
}