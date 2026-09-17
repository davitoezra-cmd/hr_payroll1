<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollCorrection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PayrollCorrectionController extends Controller
{
    /**
     * --------------------------------------------------------------------------
     * List Payroll Correction
     * --------------------------------------------------------------------------
     *
     * Bisa difilter berdasarkan:
     * - payroll_id
     * - employee_id
     * - type
     *
     */
    public function index(Request $request)
    {
        $query = PayrollCorrection::with([
            'employee',
            'payroll',
            'finance'
        ]);

        if ($request->filled('payroll_id')) {
            $query->where('payroll_id', $request->payroll_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $corrections = $query
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar payroll correction.',
            'data' => $corrections,
        ], 200);
    }

    /**
     * --------------------------------------------------------------------------
     * Detail Payroll Correction
     * --------------------------------------------------------------------------
     */
    public function show($id)
    {
        $correction = PayrollCorrection::with([
            'employee',
            'payroll',
            'finance'
        ])->find($id);

        if (!$correction) {

            return response()->json([
                'success' => false,
                'message' => 'Payroll correction tidak ditemukan.',
                'data' => null,
            ], 404);

        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil detail payroll correction.',
            'data' => $correction,
        ], 200);
    }

        /**
     * --------------------------------------------------------------------------
     * Tambah Payroll Correction
     * --------------------------------------------------------------------------
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'payroll_id' => [
                'required',
                'exists:payrolls,id',
            ],

            'employee_id' => [
                'required',
                'exists:employees,id',
            ],

            'type' => [
                'required',
                'in:addition,deduction',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:1',
            ],

            'reason' => [
                'required',
                'string',
                'max:1000',
            ],

        ]);

        if ($validator->fails()) {

            return response()->json([

                'success' => false,
                'message' => 'Validasi gagal.',
                'data' => $validator->errors(),

            ], 422);

        }

        $payroll = Payroll::find($request->payroll_id);

        if (!$payroll) {

            return response()->json([

                'success' => false,
                'message' => 'Payroll tidak ditemukan.',
                'data' => null,

            ], 404);

        }

        // Pastikan payroll milik employee yang dipilih
        if ($payroll->employee_id != $request->employee_id) {

            return response()->json([

                'success' => false,
                'message' => 'Employee tidak sesuai dengan payroll.',
                'data' => null,

            ], 422);

        }

        DB::beginTransaction();
        $correction = PayrollCorrection::create([

            'payroll_id' => $payroll->id,

            'employee_id' => $request->employee_id,

            'finance_id' => auth()->user()->id,

            'type' => $request->type,

            'amount' => $request->amount,

            'reason' => $request->reason,

        ]);



        $this->recalculatePayroll($payroll);
        DB::commit();

        return response()->json([

            'success' => true,

            'message' => 'Payroll correction berhasil ditambahkan.',

            'data' => $correction->load([
                'employee',
                'payroll',
                'finance'
            ])

        ], 201);
    }

        /**
     * Update payroll correction
     */
    public function update(Request $request, PayrollCorrection $payrollCorrection)
    {
        $validator = Validator::make($request->all(), [
           'type' => 'required|in:addition,deduction',
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {

           $payrollCorrection->update([

         'type' => $request->type,

         'amount' => $request->amount,

         'reason' => $request->reason,

]);

$this->recalculatePayroll(
    $payrollCorrection->payroll
);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payroll correction berhasil diperbarui.',
                'data' => $payrollCorrection->fresh([
                    'employee',
                    'payroll',
                    'finance'
                ]),
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui payroll correction.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus payroll correction
     */
    public function destroy(PayrollCorrection $payrollCorrection)
    {
        DB::beginTransaction();

        try {

            $payroll = $payrollCorrection->payroll;

            $payrollCorrection->delete();

            if ($payroll) {
                $this->recalculatePayroll($payroll);
            }
            

           

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payroll correction berhasil dihapus.',
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus payroll correction.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

        /**
     * --------------------------------------------------------------------------
     * Recalculate Payroll
     * --------------------------------------------------------------------------
     *
     * Menghitung ulang total koreksi payroll berdasarkan seluruh
     * payroll correction yang masih aktif.
     */
    private function recalculatePayroll(Payroll $payroll): void
    {
        $corrections = PayrollCorrection::where(
            'payroll_id',
            $payroll->id
        )->get();

        $totalKoreksi = 0;

        foreach ($corrections as $correction) {

            if ($correction->type === 'addition') {

                $totalKoreksi += $correction->amount;

            } elseif ($correction->type === 'deduction') {

                $totalKoreksi -= $correction->amount;

            }

        }

        $payroll->total_koreksi = $totalKoreksi;

        $payroll->take_home_pay =
        ($payroll->total_gaji_dasar ?? 0)
         + ($payroll->bonus_datang_awal ?? 0)
         + ($payroll->total_lembur ?? 0)
         + $totalKoreksi
         - ($payroll->total_potongan ?? 0)
         - ($payroll->total_kasbon ?? 0);

        if ($payroll->take_home_pay < 0) {
            $payroll->take_home_pay = 0;
        }

        $payroll->save();
    }
}
