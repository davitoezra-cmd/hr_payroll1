<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyPost;
use App\Models\Employee;
use App\Notifications\NewCompanyPostNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CompanyPostController extends Controller
{
    /**
     * Menampilkan semua informasi untuk Admin.
     */
    public function index(Request $request)
    {
        $query = CompanyPost::with('creator')
            ->latest('created_at');

        // Filter status jika dikirim
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter jenis jika dikirim
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $posts = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Data informasi berhasil diambil.',
            'data' => $posts,
        ]);
    }

    /**
     * Menampilkan informasi yang sudah dipublish
     * untuk Employee.
     */
    public function published(Request $request)
    {
        $query = CompanyPost::where('status', 'published')
            ->latest('created_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $posts = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Informasi berhasil diambil.',
            'data' => $posts,
        ]);
    }

    /**
     * Menampilkan satu informasi.
     */
    public function show(CompanyPost $companyPost)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail informasi berhasil diambil.',
            'data' => $companyPost->load('creator'),
        ]);
    }

    /**
     * Membuat informasi baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                Rule::in([
                    'announcement',
                    'update',
                    'article',
                    'hr_info',
                ]),
            ],

            'content' => [
                'required',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'draft',
                    'published',
                ]),
            ],
        ]);

        $imagePath = null;

        // Upload gambar jika ada
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('company-posts', 'public');
        }

        $status = $validated['status'] ?? 'draft';

        $post = CompanyPost::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'image' => $imagePath,
            'status' => $status,
            'created_by' => auth()->id(),
        ]);
        // Kirim notifikasi hanya jika langsung dipublish
            if ($status === 'published') {
                Employee::each(function ($employee) use ($post) {
                 $employee->notify(
                     new NewCompanyPostNotification($post)
        );
        });
    }

        return response()->json([
            'success' => true,
            'message' => $status === 'published'
                ? 'Informasi berhasil dipublikasikan.'
                : 'Informasi berhasil disimpan sebagai draft.',
            'data' => $post->load('creator'),
        ], 201);
    }

    /**
     * Mengubah informasi.
     */
    public function update(
        Request $request,
        CompanyPost $companyPost
    ) {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                Rule::in([
                    'announcement',
                    'update',
                    'article',
                    'hr_info',
                ]),
            ],

            'content' => [
                'required',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'status' => [
                'required',
                Rule::in([
                    'draft',
                    'published',
                ]),
            ],
        ]);

        // Upload gambar baru jika ada
        if ($request->hasFile('image')) {

            // Hapus gambar lama
            if ($companyPost->image) {
                Storage::disk('public')->delete(
                    $companyPost->image
                );
            }

            $validated['image'] = $request->file('image')
                ->store('company-posts', 'public');
        }

        $companyPost->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Informasi berhasil diperbarui.',
            'data' => $companyPost
                ->fresh()
                ->load('creator'),
        ]);
    }

    /**
     * Menghapus informasi.
     */
    public function destroy(CompanyPost $companyPost)
    {
        if ($companyPost->image) {
            Storage::disk('public')->delete(
                $companyPost->image
            );
        }

        $companyPost->delete();

        return response()->json([
            'success' => true,
            'message' => 'Informasi berhasil dihapus.',
        ]);
    }

    /**
     * Publish sebuah informasi.
     */
    public function publish(CompanyPost $companyPost)
    {
        // Cek status sebelum diubah
    $wasAlreadyPublished = $companyPost->status === 'published';

    $companyPost->update([
        'status' => 'published',
    ]);

    // Kirim notif hanya saat benar-benar berubah menjadi published
    if (!$wasAlreadyPublished) {
        Employee::each(function ($employee) use ($companyPost) {
            $employee->notify(
                new NewCompanyPostNotification($companyPost)
            );
        });
    }
        return response()->json([
            'success' => true,
            'message' => 'Informasi berhasil dipublikasikan.',
            'data' => $companyPost
                ->fresh()
                ->load('creator'),
        ]);
    }

    /**
     * Mengubah artikel menjadi draft.
     */
    public function unpublish(CompanyPost $companyPost)
    {
        $companyPost->update([
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Informasi berhasil dikembalikan ke draft.',
            'data' => $companyPost
                ->fresh()
                ->load('creator'),
        ]);
    }
}