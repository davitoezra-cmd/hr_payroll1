<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceLocationController extends Controller
{
    /**
     * Menampilkan semua lokasi absensi.
     */
    public function index(): JsonResponse
    {
        $locations = AttendanceLocation::latest('id')->get();

        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }

    /**
     * Menyimpan lokasi absensi baru.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',

            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'radius_meter' => [
                'required',
                'integer',
                'min:1',
                'max:10000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ], [
            'name.required' => 'Nama lokasi wajib diisi.',

            'latitude.required' => 'Latitude wajib diisi.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus antara -90 sampai 90.',

            'longitude.required' => 'Longitude wajib diisi.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus antara -180 sampai 180.',

            'radius_meter.required' => 'Radius absensi wajib diisi.',
            'radius_meter.integer' => 'Radius harus berupa angka bulat.',
            'radius_meter.min' => 'Radius minimal 1 meter.',
            'radius_meter.max' => 'Radius maksimal 10.000 meter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data lokasi tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
         * Jika lokasi baru dibuat aktif,
         * nonaktifkan lokasi aktif lainnya.
         *
         * Untuk tahap awal kita menggunakan satu lokasi aktif
         * sebagai lokasi utama absensi.
         */
        if ($request->boolean('is_active', true)) {
            AttendanceLocation::where('is_active', true)
                ->update(['is_active' => false]);
        }

        $location = AttendanceLocation::create([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meter' => $request->radius_meter,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi absensi berhasil ditambahkan.',
            'data' => $location,
        ], 201);
    }

    /**
     * Menampilkan detail satu lokasi.
     */
    public function show(AttendanceLocation $attendanceLocation): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $attendanceLocation,
        ]);
    }

    /**
     * Mengubah lokasi absensi.
     */
    public function update(
        Request $request,
        AttendanceLocation $attendanceLocation
    ): JsonResponse {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',

            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],

            'radius_meter' => [
                'required',
                'integer',
                'min:1',
                'max:10000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ], [
            'name.required' => 'Nama lokasi wajib diisi.',

            'latitude.required' => 'Latitude wajib diisi.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus antara -90 sampai 90.',

            'longitude.required' => 'Longitude wajib diisi.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus antara -180 sampai 180.',

            'radius_meter.required' => 'Radius absensi wajib diisi.',
            'radius_meter.integer' => 'Radius harus berupa angka bulat.',
            'radius_meter.min' => 'Radius minimal 1 meter.',
            'radius_meter.max' => 'Radius maksimal 10.000 meter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data lokasi tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
         * Jika lokasi ini diaktifkan,
         * nonaktifkan lokasi aktif lainnya.
         */
        if ($request->boolean('is_active', false)) {
            AttendanceLocation::where('id', '!=', $attendanceLocation->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $attendanceLocation->update([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meter' => $request->radius_meter,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi absensi berhasil diperbarui.',
            'data' => $attendanceLocation->fresh(),
        ]);
    }

    /**
     * Mengaktifkan / menonaktifkan lokasi.
     */
    public function toggleStatus(
        AttendanceLocation $attendanceLocation
    ): JsonResponse {
        $newStatus = !$attendanceLocation->is_active;

        /*
         * Hanya satu lokasi yang boleh aktif.
         */
        if ($newStatus) {
            AttendanceLocation::where('id', '!=', $attendanceLocation->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $attendanceLocation->update([
            'is_active' => $newStatus,
        ]);

        return response()->json([
            'success' => true,
            'message' => $newStatus
                ? 'Lokasi absensi berhasil diaktifkan.'
                : 'Lokasi absensi berhasil dinonaktifkan.',
            'data' => $attendanceLocation->fresh(),
        ]);
    }

    /**
     * Menghapus lokasi absensi.
     */
    public function destroy(
        AttendanceLocation $attendanceLocation
    ): JsonResponse {
        $attendanceLocation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lokasi absensi berhasil dihapus.',
        ]);
    }
}

