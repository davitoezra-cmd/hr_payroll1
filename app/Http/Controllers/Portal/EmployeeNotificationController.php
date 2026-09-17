<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeNotificationController extends Controller
{
    /**
     * Mengambil semua notifikasi Employee
     */
    public function index(Request $request)
    {
        $employee = $request->user();

        $notifications = $employee->notifications()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Mengambil jumlah notifikasi yang belum dibaca
     */
    public function unreadCount(Request $request)
    {
        $employee = $request->user();

        $count = $employee->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
            ],
        ]);
    }

    /**
     * Menandai satu notifikasi sebagai sudah dibaca
     */
    public function markAsRead(
        Request $request,
        string $id
    ) {
        $employee = $request->user();

        $notification = $employee
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditandai sudah dibaca.',
        ]);
    }

    /**
     * Menandai semua notifikasi sebagai sudah dibaca
     */
    public function markAllAsRead(Request $request)
    {
        $employee = $request->user();

        $employee->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi berhasil ditandai sudah dibaca.',
        ]);
    }
}