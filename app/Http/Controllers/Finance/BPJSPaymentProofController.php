<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BPJSPaymentProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BPJSPaymentProofController extends Controller
{
    /**
     * Daftar bukti pembayaran BPJS.
     */
    public function index(Request $request)
    {
        $query = BPJSPaymentProof::with([
    'finance',
    'employee',
]);
        if ($request->filled('bpjs_type')) {
            $query->where('bpjs_type', $request->bpjs_type);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $proofs = $query->latest()->get();

        $proofs->each(function ($item) {
    $item->file_url = asset('storage/' . $item->file_path);
});

        return response()->json([
            'success' => true,
            'count'   => $proofs->count(),
            'data'    => $proofs,
        ]);
        
    }

    /**
     * Simpan bukti pembayaran.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bpjs_type'     => 'required|in:kesehatan,ketenagakerjaan',
            'period'        => 'required|string|max:20',
            'document_name' => 'required|string|max:255',
            'file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');

        $path = $file->store('bpjs', 'public');

        $proof = BPJSPaymentProof::create([
            'finance_id'    => auth()->id(),
            'employee_id' => $request->employee_id,
            'bpjs_type'     => $request->bpjs_type,
            'period'        => $request->period,
            'document_name' => $request->document_name,
            'file_path'     => $path,
            'notes'         => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran BPJS berhasil diupload.',
            'data'    => $proof,
        ], 201);
    }

    /**
     * Detail bukti pembayaran.
     */
    public function show($id)
    {
       $proof = BPJSPaymentProof::with([
    'finance',
    'employee',
])->find($id);

        if (!$proof) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $proof->file_url = asset('storage/' . $proof->file_path);

        return response()->json([
            'success' => true,
            'data'    => $proof,
        ]);
    }

    /**
     * Update bukti pembayaran.
     */
    public function update(Request $request, $id)
    {
        $proof = BPJSPaymentProof::find($id);

        if (!$proof) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'bpjs_type'     => 'required|in:kesehatan,ketenagakerjaan',
            'period'        => 'required|string|max:20',
            'document_name' => 'required|string|max:255',
            'file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile('file')) {

            if ($proof->file_path && Storage::disk('public')->exists($proof->file_path)) {
                Storage::disk('public')->delete($proof->file_path);
            }

            $proof->file_path = $request->file('file')->store('bpjs', 'public');
        }

        $proof->update([
            'bpjs_type'     => $request->bpjs_type,
            'period'        => $request->period,
            'document_name' => $request->document_name,
            'file_path'     => $proof->file_path,
            'notes'         => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui.',
            'data'    => $proof,
        ]);
    }

    /**
     * Hapus bukti pembayaran.
     */
    public function destroy($id)
    {
        $proof = BPJSPaymentProof::find($id);

        if (!$proof) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        if ($proof->file_path && Storage::disk('public')->exists($proof->file_path)) {
            Storage::disk('public')->delete($proof->file_path);
        }

        $proof->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus.',
        ]);
    }
}