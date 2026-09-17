<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CompanyPost;
use Illuminate\Http\Request;

class CompanyPostController extends Controller
{
    /**
     * Menampilkan informasi yang sudah dipublikasikan
     * untuk Employee.
     *
     * Publikasi ditentukan hanya berdasarkan:
     * status = published
     *
     * Waktu publikasi menggunakan created_at.
     * Laravel menyimpan created_at dalam UTC.
     */
    public function index(Request $request)
    {
        $query = CompanyPost::query()
            ->where('status', 'published')
            ->latest('created_at');

        // Filter berdasarkan jenis jika dikirim
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
     * Menampilkan detail informasi yang sudah dipublikasikan.
     *
     * Employee hanya boleh melihat post
     * dengan status published.
     */
    public function show(CompanyPost $companyPost)
    {
        // Employee tidak boleh melihat draft
        if ($companyPost->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Informasi tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail informasi berhasil diambil.',
            'data' => $companyPost,
        ]);
    }
}