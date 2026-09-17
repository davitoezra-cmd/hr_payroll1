<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\BPJSPaymentProof;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class BPJSPaymentProofController extends Controller
{
    /**
     * Menampilkan seluruh bukti BPJS milik employee yang login.
     */
    public function index()
    {
        $employee = auth()->user();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee belum login.'
            ], 401);
        }

        $data = BPJSPaymentProof::with('finance')
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'data' => $data
        ]);
    }

    /**
     * Detail bukti BPJS.
     */
    public function show($id)
    {
        $employee = auth()->user();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee belum login.'
            ], 401);
        }

        $proof = BPJSPaymentProof::with('finance')
            ->where('employee_id', $employee->id)
            ->find($id);

        if (!$proof) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $proof
        ]);
    }

    public function download($id)
{
    $proof = BPJSPaymentProof::findOrFail($id);

    if (!$proof->file_path) {
        return response()->json([
            'message' => 'File tidak ditemukan.'
        ], 404);
    }

    if (!Storage::disk('public')->exists($proof->file_path)) {
        return response()->json([
            'message' => 'File fisik tidak ditemukan di storage.'
        ], 404);
    }

    $path = Storage::disk('public')->path($proof->file_path);

    $extension = strtolower(
        pathinfo($proof->file_path, PATHINFO_EXTENSION)
    );

    $mimeTypes = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'bmp'  => 'image/bmp',
    ];

    $mimeType = $mimeTypes[$extension]
        ?? mime_content_type($path)
        ?? 'application/octet-stream';

    $fileName = $proof->document_name
        ?: 'Bukti_BPJS_' . $proof->id;

    if (!pathinfo($fileName, PATHINFO_EXTENSION)) {
        $fileName .= '.' . ($extension ?: 'file');
    }

    return response()->download(
        $path,
        $fileName,
        [
            'Content-Type' => $mimeType,
            'Content-Disposition' =>
                'attachment; filename="' . $fileName . '"',
        ]
    );
}
}