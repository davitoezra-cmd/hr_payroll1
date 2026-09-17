<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceQr;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttendanceQrController extends Controller
{
    /**
     * Menampilkan semua QR Code
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => AttendanceQr::latest()->paginate(10)
        ]);
    }

    /**
 * Menampilkan QR Code yang sedang aktif
 */
public function show()
{
    $qr = AttendanceQr::where('is_active', true)
        ->latest()
        ->first();

    if (!$qr) {
        return response()->json([
            'success' => false,
            'message' => 'QR Code aktif tidak ditemukan.',
        ], 404);
    }

    // Jika QR memiliki masa berlaku dan sudah expired
    if ($qr->expired_at && now()->greaterThan($qr->expired_at)) {

        $qr->update([
            'is_active' => false,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'QR Code sudah kedaluwarsa.',
        ], 410);
    }

    return response()->json([
        'success' => true,
        'message' => 'QR Code aktif ditemukan.',
        'data' => [
            'id'         => $qr->id,
            'name'       => $qr->name,
            'token'      => $qr->token,
             'image'      => asset('storage/' . $qr->image),
            'is_active'  => $qr->is_active,
            'expired_at' => $qr->expired_at,
            'created_at' => $qr->created_at,
           
        ]
    ]);
}


    /**
 * Generate QR Code baru
 */
public function generate(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:100',
    ]);

    // Nonaktifkan QR yang sedang aktif
    AttendanceQr::where('is_active', true)->update([
        'is_active' => false,
    ]);

    $token = (string) Str::uuid();

    $filename = 'qr_' . time() . '.png';

    $builder = new Builder(
        writer: new PngWriter(),
        data: $token,
        encoding: new Encoding('UTF-8'),
        errorCorrectionLevel: ErrorCorrectionLevel::Low,
        size: 400,
        margin: 10,
    );

    $result = $builder->build();

    Storage::disk('public')->put(
        'qrcodes/' . $filename,
        $result->getString()
    );

    $qr = AttendanceQr::create([
        'name'       => $request->name,
        'token'      => $token,
        'image'      => 'qrcodes/' . $filename,
        'is_active'  => true,
        'expired_at' => null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'QR Code berhasil dibuat.',
        'data' => [
            'id'         => $qr->id,
            'name'       => $qr->name,
            'token'      => $qr->token,
            'image'      => asset('storage/' . $qr->image),
            'is_active'  => $qr->is_active,
        ]
    ], 201);
}
    /**
     * Generate QR Code baru
     */
   public function regenerate($id)
{
    $qr = AttendanceQr::findOrFail($id);

    // Nonaktifkan QR aktif
    AttendanceQr::where('is_active', true)
        ->update([
            'is_active' => false,
        ]);

    // Hapus gambar lama
    if ($qr->image && Storage::disk('public')->exists($qr->image)) {
        Storage::disk('public')->delete($qr->image);
    }

    $token = (string) Str::uuid();

    $filename = 'qr_' . time() . '.png';

    $builder = new Builder(
        writer: new PngWriter(),
        data: $token,
        encoding: new Encoding('UTF-8'),
        errorCorrectionLevel: ErrorCorrectionLevel::Low,
        size: 400,
        margin: 10,
    );

    $result = $builder->build();

    Storage::disk('public')->put(
        'qrcodes/' . $filename,
        $result->getString()
    );

    $qr->update([
        'token'      => $token,
        'image'      => 'qrcodes/' . $filename,
        'is_active'  => true,
        'expired_at' => null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'QR berhasil diperbarui.',
        'data'    => [
            'id'         => $qr->id,
            'name'       => $qr->name,
            'token'      => $qr->token,
            'image'      => asset('storage/' . $qr->image),
            'is_active'  => $qr->is_active,
        ]
    ]);
}
    public function setExpired(Request $request,$id)
{
    $request->validate([
        'expired_at'=>'required|date'
    ]);

    $qr=AttendanceQr::findOrFail($id);

    $qr->update([
        'expired_at'=>$request->expired_at
    ]);

    return response()->json([
    'success' => true,
    'message' => 'Masa berlaku QR berhasil diperbarui.',
    'data'    => $qr,
]);
}



    /**
     * Menonaktifkan QR Code
     */
    public function deactivate($id)
    {
        $qr = AttendanceQr::findOrFail($id);

        $qr->update([
            'is_active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'QR Code berhasil dinonaktifkan.',
        ]);
    }

    /**
     * Mengaktifkan kembali QR Code
     */
    public function activate($id)
    {
        $qr = AttendanceQr::findOrFail($id);

        AttendanceQr::where('is_active',true)
        ->update([
        'is_active'=>false
    ]);
        $qr->update([
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'QR Code berhasil diaktifkan.',
        ]);
    }

    /**
 * Scan QR Code untuk absensi
 */

    /**
     * Hapus QR Code
     */
    public function destroy($id)
    {
        AttendanceQr::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'QR Code berhasil dihapus.',
        ]);
    }

    public function download($id)
{
    $qr = AttendanceQr::findOrFail($id);

    if (!$qr->image) {
        return response()->json([
            'success' => false,
            'message' => 'QR Code belum memiliki gambar.',
        ], 404);
    }

    $disk = Storage::disk('public');

    if (!$disk->exists($qr->image)) {
        return response()->json([
            'success' => false,
            'message' => 'File QR Code tidak ditemukan.',
            'path' => $qr->image,
        ], 404);
    }

    return response()->download(
        $disk->path($qr->image),
        'QR-' . $qr->id . '.png',
        [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]
    );
}

    public function image($id)
{
    $qr = AttendanceQr::findOrFail($id);

    if (!$qr->image) {
        return response()->json([
            'success' => false,
            'message' => 'QR Code belum memiliki gambar.',
        ], 404);
    }

    $disk = Storage::disk('public');

    if (!$disk->exists($qr->image)) {
        return response()->json([
            'success' => false,
            'message' => 'File QR Code tidak ditemukan.',
            'path' => $qr->image,
        ], 404);
    }

    $filePath = $disk->path($qr->image);

    return response()->file($filePath, [
        'Content-Type' => 'image/png',
        'Content-Disposition' => 'inline; filename="QR-' . $qr->id . '.png"',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ]);
}
    public function statistics()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total' => AttendanceQr::count(),
                'active' => AttendanceQr::where('is_active', true)->count(),
                'inactive' => AttendanceQr::where('is_active', false)->count(),
                'expired' => AttendanceQr::whereNotNull('expired_at')
                    ->where('expired_at', '<', now())
                    ->count(),
            ]
        ]);
    }
}

