<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\BusinessTrip;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessTripController extends Controller
{
    protected WhatsAppService $whatsapp;

public function __construct(WhatsAppService $whatsapp)
{
    $this->whatsapp = $whatsapp;
}
    /**
     * Daftar pengajuan dinas luar milik employee.
     */
    public function index(Request $request)
    {
        $employee = $request->user();

        $businessTrips = BusinessTrip::where('employee_id', $employee->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $businessTrips
        ]);
    }

    /**
     * Pengajuan dinas luar.
     */
    
public function store(Request $request)
{
    $employee = $request->user();

    $request->validate([
        'trip_date'   => 'required|date',
        'destination' => 'required|string|max:255',
        'purpose'     => 'required|string|max:1000',
    ]);

    $businessTrip = BusinessTrip::create([
        'employee_id' => $employee->id,
        'trip_date'   => $request->trip_date,
        'destination' => $request->destination,
        'purpose'     => $request->purpose,
        'status'      => 'pending',
    ]);

    /*
    |--------------------------------------------------------------------------
    | KIRIM WHATSAPP KE ADMIN
    |--------------------------------------------------------------------------
    */

     $admin = \App\Models\User::find(1);

$adminPhone = $admin?->phone;


    $approvalUrl = 'https://absen.wuznet.com/approval';

    $tripDate = \Carbon\Carbon::parse($businessTrip->trip_date)
        ->format('d-m-Y');

    $message =
        "{$employee->name} telah mengajukan untuk dinas luar.\n\n" .
        "Tanggal: {$tripDate}\n" .
        "Tujuan: {$businessTrip->destination}\n" .
        "Keperluan: {$businessTrip->purpose}\n\n" .
        "Silahkan melakukan approval dengan mengklik link berikut:\n" .
        $approvalUrl;

    try {
        $this->whatsapp->send(
            $adminPhone,
            $message
        );
    } catch (\Throwable $e) {
        \Log::error('Gagal mengirim WhatsApp pengajuan dinas luar', [
            'business_trip_id' => $businessTrip->id,
            'employee_id' => $employee->id,
            'error' => $e->getMessage(),
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Pengajuan dinas luar berhasil dikirim.',
        'data' => $businessTrip
    ], 201);
}



    /**
     * Detail pengajuan.
     */
    public function show(Request $request, $id)
    {
        $employee = $request->user();

        $businessTrip = BusinessTrip::where('employee_id', $employee->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $businessTrip
        ]);
    }

    /**
     * Hapus jika masih pending.
     */
    public function destroy(Request $request, $id)
    {
        $employee = $request->user();

        $businessTrip = BusinessTrip::where('employee_id', $employee->id)
            ->findOrFail($id);

        if ($businessTrip->status != 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan yang sudah diproses tidak dapat dihapus.'
            ], 400);
        }

        $businessTrip->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil dihapus.'
        ]);
    }

    /**
     * Check In Dinas Luar.
     */
    public function checkIn(Request $request, $id)
    {
        $employee = $request->user();

        $request->validate([
            'photo' => 'required|image|max:2048',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $businessTrip = BusinessTrip::where('employee_id', $employee->id)
            ->findOrFail($id);

        if ($businessTrip->status != 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan belum disetujui.'
            ], 400);
        }

        if ($businessTrip->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Check In sudah dilakukan.'
            ], 400);
        }

        $photo = $request->file('photo')
            ->store('business-trip/checkin', 'public');

        $businessTrip->update([
            'check_in' => now()->format('H:i:s'),
            'check_in_photo' => $photo,
            'check_in_latitude' => $request->latitude,
            'check_in_longitude' => $request->longitude,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check In berhasil.',
            'data' => $businessTrip->fresh()
        ]);
    }

    /**
     * Check Out Dinas Luar.
     */
    public function checkOut(Request $request, $id)
    {
        $employee = $request->user();

        $request->validate([
            'photo' => 'required|image|max:2048',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $businessTrip = BusinessTrip::where('employee_id', $employee->id)
            ->findOrFail($id);

        if (!$businessTrip->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan Check In terlebih dahulu.'
            ], 400);
        }

        if ($businessTrip->check_out) {
            return response()->json([
                'success' => false,
                'message' => 'Check Out sudah dilakukan.'
            ], 400);
        }

        $photo = $request->file('photo')
            ->store('business-trip/checkout', 'public');

        $businessTrip->update([
            'check_out' => now()->format('H:i:s'),
            'check_out_photo' => $photo,
            'check_out_latitude' => $request->latitude,
            'check_out_longitude' => $request->longitude,
            'status' => 'completed'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check Out berhasil.',
            'data' => $businessTrip->fresh()
        ]);
    }
}