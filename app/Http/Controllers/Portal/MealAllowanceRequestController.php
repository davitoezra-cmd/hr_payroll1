<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\MealAllowanceRequest;
use App\Models\User;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MealAllowanceRequestController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Daftar pengajuan uang makan milik employee.
     */
    public function index()
    {
        $employee = Auth::user();

        $mealAllowances = MealAllowanceRequest::where(
            'employee_id',
            $employee->id
        )
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $mealAllowances
        ]);
    }

    /**
     * Simpan pengajuan uang makan.
     */
    public function store(Request $request)
    {
        $employee = Auth::user();

        $validator = Validator::make($request->all(), [
            'meal_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $mealAllowance = MealAllowanceRequest::create([
            'employee_id' => $employee->id,
            'meal_date' => $request->meal_date,
            'amount' => $request->amount,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        /*
        |--------------------------------------------------------------------------
        | KIRIM WHATSAPP KE ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = User::find(1);

        $adminPhone = $admin?->phone;

        $approvalUrl = 'https://yukabsen.com/approval';

        $mealDate = Carbon::parse($mealAllowance->meal_date)
            ->format('d-m-Y');

        $amount = number_format(
            $mealAllowance->amount,
            0,
            ',',
            '.'
        );

        $message =
            "{$employee->name} telah mengajukan uang makan.\n\n" .
            "Tanggal uang makan: {$mealDate}\n" .
            "Nominal: Rp {$amount}\n" .
            "Alasan: " . ($mealAllowance->reason ?? '-') . "\n\n" .
            "Silahkan melakukan approval dengan mengklik link berikut:\n" .
            $approvalUrl;

        try {
            if ($adminPhone) {
                $this->whatsapp->send(
                    $adminPhone,
                    $message
                );
            }
        } catch (\Throwable $e) {
            // Gagal WhatsApp tidak boleh menggagalkan pengajuan
            Log::error(
                'Gagal mengirim WhatsApp pengajuan uang makan',
                [
                    'meal_allowance_id' => $mealAllowance->id,
                    'employee_id' => $employee->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan uang makan berhasil dikirim.',
            'data' => $mealAllowance
        ], 201);
    }

    /**
     * Detail pengajuan uang makan.
     */
    public function show($id)
    {
        $employee = Auth::user();

        $mealAllowance = MealAllowanceRequest::where(
            'employee_id',
            $employee->id
        )
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $mealAllowance
        ]);
    }

    /**
     * Hapus jika masih pending.
     */
    public function destroy($id)
    {
        $employee = Auth::user();

        $mealAllowance = MealAllowanceRequest::where(
            'employee_id',
            $employee->id
        )
            ->findOrFail($id);

        if ($mealAllowance->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan yang sudah diproses tidak dapat dihapus.'
            ], 400);
        }

        $mealAllowance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan uang makan berhasil dihapus.'
        ]);
    }
}