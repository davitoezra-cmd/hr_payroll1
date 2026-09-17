<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\EmployeePayrollSetting;
use Illuminate\Http\Request;

class EmployeePayrollSettingController extends Controller
{
    /**
     * =========================================================
     * INDEX
     * =========================================================
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => EmployeePayrollSetting::with('employee')
                ->latest()
                ->get(),
        ]);
    }

    /**
     * =========================================================
     * STORE
     * =========================================================
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => [
                'required',
                'exists:employees,id',
                'unique:employee_payroll_settings,employee_id',
            ],

            /**
             * GAJI
             */
            'gaji_harian' =>
                'nullable|numeric|min:0',

            /**
             * BONUS DATANG AWAL
             *
             * bonus_datang_awal
             * = nominal bonus per hari
             *
             * mulai_bonus_datang
             * = batas jam untuk mendapatkan bonus
             *
             * Contoh:
             *
             * mulai_bonus_datang = 07:30
             * bonus_datang_awal = 10000
             *
             * Check-in 07:20
             * -> dapat Rp10.000
             */
            'bonus_datang_awal' =>
                'nullable|numeric|min:0',

            'mulai_bonus_datang' =>
                'nullable|date_format:H:i',

            /**
             * BONUS KEDISIPLINAN
             *
             * Diberikan 1x setelah periode selesai
             * apabila employee memenuhi syarat.
             *
             * Contoh:
             *
             * bonus_kedisiplinan = 100000
             */
            'bonus_kedisiplinan' =>
                'nullable|numeric|min:0',

            /**
             * LEMBUR
             */
            'tarif_lembur' =>
                'nullable|numeric|min:0',

            /**
             * POTONGAN
             */
            'potongan_terlambat' =>
                'nullable|numeric|min:0',

            'potongan_izin' =>
                'nullable|numeric|min:0',

            'potongan_cuti' =>
                'nullable|numeric|min:0',

            /**
             * HAK
             */
            'jatah_hari_libur' =>
                'nullable|integer|min:0',

            'jatah_cuti' =>
                'nullable|integer|min:0',

            /**
             * TANGGAL GAJIAN
             */
            'tanggal_gajian' =>
                'nullable|integer|min:1|max:31',

            /**
             * STATUS
             */
            'aktif' =>
                'nullable|boolean',
        ]);

        $setting = EmployeePayrollSetting::create(
            $validated
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Setting payroll berhasil dibuat.',
            'data' =>
                $setting->load('employee'),
        ], 201);
    }

    /**
     * =========================================================
     * SHOW
     * =========================================================
     */
    public function show(
        EmployeePayrollSetting $employeePayrollSetting
    ) {
        return response()->json([
            'success' => true,
            'data' =>
                $employeePayrollSetting
                    ->load('employee'),
        ]);
    }

    /**
     * =========================================================
     * UPDATE
     * =========================================================
     */
    public function update(
        Request $request,
        EmployeePayrollSetting $employeePayrollSetting
    ) {
        $validated = $request->validate([
            /**
             * GAJI
             */
            'gaji_harian' =>
                'nullable|numeric|min:0',

            /**
             * BONUS DATANG AWAL
             */
            'bonus_datang_awal' =>
                'nullable|numeric|min:0',

            'mulai_bonus_datang' =>
                'nullable|date_format:H:i',

            /**
             * BONUS KEDISIPLINAN
             */
            'bonus_kedisiplinan' =>
                'nullable|numeric|min:0',

            /**
             * LEMBUR
             */
            'tarif_lembur' =>
                'nullable|numeric|min:0',

            /**
             * POTONGAN
             */
            'potongan_terlambat' =>
                'nullable|numeric|min:0',

            'potongan_izin' =>
                'nullable|numeric|min:0',

            'potongan_cuti' =>
                'nullable|numeric|min:0',

            /**
             * HAK
             */
            'jatah_hari_libur' =>
                'nullable|integer|min:0',

            'jatah_cuti' =>
                'nullable|integer|min:0',

            /**
             * TANGGAL GAJIAN
             */
            'tanggal_gajian' =>
                'nullable|integer|min:1|max:31',

            /**
             * STATUS
             */
            'aktif' =>
                'nullable|boolean',
        ]);

        $employeePayrollSetting->update(
            $validated
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Setting payroll berhasil diperbarui.',
            'data' =>
                $employeePayrollSetting
                    ->load('employee'),
        ]);
    }

    /**
     * =========================================================
     * DESTROY
     * =========================================================
     */
    public function destroy(
        EmployeePayrollSetting $employeePayrollSetting
    ) {
        $employeePayrollSetting->delete();

        return response()->json([
            'success' => true,
            'message' =>
                'Setting payroll berhasil dihapus.',
        ]);
    }
}