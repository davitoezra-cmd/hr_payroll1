<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\MedicalLeave;
use App\Models\User;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MedicalLeaveController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Daftar pengajuan sakit milik employee.
     */
    public function index()
    {
        $employee = Auth::user();

        $medicalLeaves = MedicalLeave::where(
            'employee_id',
            $employee->id
        )
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $medicalLeaves
        ]);
    }

    /**
     * Simpan pengajuan sakit.
     */
    public function store(Request $request)
    {
        $employee = Auth::user();

        $validator = Validator::make($request->all(), [
            'sick_date' => 'required|date',
            'reason' => 'required|string',
            'doctor_note' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengajuan tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $doctorNote = null;

        if ($request->hasFile('doctor_note')) {
            $doctorNote = $request
                ->file('doctor_note')
                ->store('doctor-notes', 'public');
        }

        $medicalLeave = MedicalLeave::create([
            'employee_id' => $employee->id,
            'sick_date' => $request->sick_date,
            'reason' => $request->reason,
            'doctor_note' => $doctorNote,
            'status' => 'pending',
        ]);

        /*
        |--------------------------------------------------------------------------
        | KIRIM WHATSAPP KE ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = User::find(1);

        $adminPhone = $admin?->phone;

        $approvalUrl = 'https://absen.wuznet.com/approval';

        $sickDate = Carbon::parse($medicalLeave->sick_date)
            ->format('d-m-Y');

        $message =
            "{$employee->name} telah mengajukan sakit.\n\n" .
            "Tanggal sakit: {$sickDate}\n" .
            "Alasan: {$medicalLeave->reason}\n\n" .
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
            // Gagal WhatsApp tidak boleh menggagalkan pengajuan sakit
            Log::error(
                'Gagal mengirim WhatsApp pengajuan sakit',
                [
                    'medical_leave_id' => $medicalLeave->id,
                    'employee_id' => $employee->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan sakit berhasil dikirim.',
            'data' => $medicalLeave
        ], 201);
    }

    /**
     * Detail pengajuan sakit.
     */
    public function show($id)
    {
        $employee = Auth::user();

        $medicalLeave = MedicalLeave::where(
            'employee_id',
            $employee->id
        )
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $medicalLeave
        ]);
    }

    /**
     * Hapus jika masih pending.
     */
    public function destroy($id)
    {
        $employee = Auth::user();

        $medicalLeave = MedicalLeave::where(
            'employee_id',
            $employee->id
        )
            ->findOrFail($id);

        if ($medicalLeave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan yang sudah diproses tidak dapat dihapus.'
            ], 400);
        }

        $medicalLeave->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil dihapus.'
        ]);
    }
}