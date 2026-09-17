<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DocumentController extends Controller
{
    /**
     * Menampilkan semua dokumen.
     */
    public function index(Request $request)
    {
        $query = Document::with('uploader')
            ->latest();

        // Search berdasarkan nama dokumen
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter kategori
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $documents = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Data dokumen berhasil diambil.',
            'data' => $documents,
        ]);
    }

    /**
     * Menampilkan detail dokumen.
     */
    public function show(Document $document)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail dokumen berhasil diambil.',
            'data' => $document->load('uploader'),
        ]);
    }

    /**
     * Upload dokumen baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:10240',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');

        // Simpan file ke storage/app/public/documents
        $path = $file->store('documents', 'public');

        $document = Document::create([
            'name' => $validated['name'],
            'category' => $validated['category'] ?? null,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $validated['description'] ?? null,
            'uploaded_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diupload.',
            'data' => $document->load('uploader'),
        ], 201);
    }

    /**
     * Update informasi dokumen.
     *
     * File baru bersifat opsional.
     */
    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:10240',
            'description' => 'nullable|string',
        ]);

        // Kalau upload file baru
        if ($request->hasFile('file')) {

            // Hapus file lama
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $file = $request->file('file');

            // Simpan file baru
            $path = $file->store('documents', 'public');

            $document->file_name = $file->getClientOriginalName();
            $document->file_path = $path;
            $document->file_size = $file->getSize();
            $document->mime_type = $file->getMimeType();
        }

        $document->name = $validated['name'];
        $document->category = $validated['category'] ?? null;
        $document->description = $validated['description'] ?? null;

        $document->save();

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diperbarui.',
            'data' => $document->load('uploader'),
        ]);
    }

    /**
     * Preview dokumen.
     *
     * File akan dikirim langsung ke browser.
     * Cocok untuk PDF dan gambar.
     */
    /**
 * Preview dokumen.
 *
 * File dikirim inline agar browser dapat menampilkannya
 * langsung, terutama untuk PDF dan gambar.
 */
public function preview(Document $document)
{
    if (!$document->file_path) {
        return response()->json([
            'success' => false,
            'message' => 'Path file dokumen tidak tersedia.',
        ], 404);
    }

    $disk = Storage::disk('public');

    if (!$disk->exists($document->file_path)) {
        return response()->json([
            'success' => false,
            'message' => 'File dokumen tidak ditemukan.',
        ], 404);
    }

    $filePath = $disk->path($document->file_path);

    $mimeType = $document->mime_type;

    if (!$mimeType) {
        $mimeType = $disk->mimeType(
            $document->file_path
        );
    }

    $filename = $document->file_name
        ?: basename($document->file_path);

    return response()->file(
        $filePath,
        [
            'Content-Type' => $mimeType,
            'Content-Disposition' =>
                'inline; filename="' .
                addslashes($filename) .
                '"',
            'Content-Length' =>
                filesize($filePath),
            'Cache-Control' =>
                'private, max-age=3600',
        ]
    );
}
    /**
     * Download dokumen.
     */
    public function download(Document $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File dokumen tidak ditemukan.',
            ], 404);
        }

        return Storage::disk('public')->download(
            $document->file_path,
            $document->file_name
        );
    }

    /**
     * Hapus dokumen.
     */
    public function destroy(Document $document)
    {
        // Hapus file dari storage
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Hapus data database
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil dihapus.',
        ]);
    }

    public function previewUrl(Document $document)
{
    if (!$document->file_path) {
        return response()->json([
            'success' => false,
            'message' => 'Path file dokumen tidak tersedia.',
        ], 404);
    }

    if (!Storage::disk('public')->exists($document->file_path)) {
        return response()->json([
            'success' => false,
            'message' => 'File dokumen tidak ditemukan.',
        ], 404);
    }

    $url = URL::temporarySignedRoute(
        'admin.documents.preview.signed',
        now()->addMinutes(5),
        [
            'document' => $document->id,
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'URL preview berhasil dibuat.',
        'data' => [
            'url' => $url,
        ],
    ]);
}
   public function previewSigned(Document $document)
{
    if (!$document->file_path) {
        abort(404, 'Path file tidak tersedia.');
    }

    $disk = Storage::disk('public');

    if (!$disk->exists($document->file_path)) {
        abort(404, 'File tidak ditemukan.');
    }

    $path = $disk->path($document->file_path);

    $mimeType = $document->mime_type
        ?: $disk->mimeType($document->file_path)
        ?: 'application/octet-stream';

    return response()->file($path, [
        'Content-Type' => $mimeType,

        'Content-Disposition' =>
            'inline; filename="' .
            addslashes($document->file_name) .
            '"',

        'Cache-Control' => 'private, max-age=300',
    ]);
}
}