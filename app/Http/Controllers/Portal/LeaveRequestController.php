<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LeaveRequestController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Daftar pengajuan cuti milik employee.
     */
    public function index(Request $request)
    {
        $employee = $request->user();

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $leaveRequests
        ]);
    }

    /**
     * Simpan pengajuan cuti.
     */
    public function store(Request $request)
    {
        $employee = $request->user();

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:cuti,izin,sakit',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengajuan tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $attachment = null;

        if ($request->hasFile('attachment')) {
            $attachment = $request
                ->file('attachment')
                ->store('leave', 'public');
        }

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment' => $attachment,
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

        $startDate = Carbon::parse($leave->start_date)
            ->format('d-m-Y');

        $endDate = Carbon::parse($leave->end_date)
            ->format('d-m-Y');

        // Ubah tipe agar lebih enak dibaca di WhatsApp
        $typeLabel = match ($leave->type) {
            'cuti' => 'Cuti',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            default => ucfirst($leave->type),
        };

        $message =
            "{$employee->name} telah mengajukan {$typeLabel}.\n\n" .
            "Tanggal: {$startDate} s/d {$endDate}\n" .
            "Alasan: {$leave->reason}\n\n" .
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
                'Gagal mengirim WhatsApp pengajuan leave',
                [
                    'leave_id' => $leave->id,
                    'employee_id' => $employee->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil dikirim.',
            'data' => $leave
        ], 201);
    }

    /**
     * Detail pengajuan.
     */
    public function show(Request $request, $id)
    {
        $employee = $request->user();

        $leave = LeaveRequest::where('employee_id', $employee->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $leave
        ]);
    }

    /**
     * Hapus pengajuan jika masih pending.
     */
    public function destroy(Request $request, $id)
    {
        $employee = $request->user();

        $leave = LeaveRequest::where('employee_id', $employee->id)
            ->findOrFail($id);

        if ($leave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan yang sudah diproses tidak bisa dihapus.'
            ], 400);
        }

        $leave->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil dihapus.'
        ]);
    }
}