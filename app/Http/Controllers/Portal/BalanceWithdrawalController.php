<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\BalanceTransaction;
use App\Models\BalanceWithdrawal;
use App\Models\EmployeeBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceWithdrawalController extends Controller
{
    /**
     * Melihat pengajuan penarikan milik employee yang sedang login.
     */
    public function index()
    {
        $employee = auth()->user();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee belum login.',
            ], 401);
        }

        $withdrawals = BalanceWithdrawal::where(
            'employee_id',
            $employee->id
        )
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'data' => $withdrawals,
        ]);
    }


    /**
     * Employee mengajukan penarikan saldo.
     */
    public function store(Request $request)
    {
        $employee = auth()->user();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee belum login.',
            ], 401);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        $balance = EmployeeBalance::where(
            'employee_id',
            $employee->id
        )->first();

        if (!$balance) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo employee belum tersedia.',
            ], 404);
        }

        if ($request->amount > $balance->balance) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah penarikan melebihi saldo.',
                'saldo' => $balance->balance,
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Cek apakah masih ada pengajuan yang belum diproses
        |--------------------------------------------------------------------------
        */

        $pending = BalanceWithdrawal::where(
            'employee_id',
            $employee->id
        )
        ->where('status', 'pending')
        ->exists();

        if ($pending) {
            return response()->json([
                'success' => false,
                'message' => 'Masih ada pengajuan penarikan yang belum diproses.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Buat pengajuan
        |--------------------------------------------------------------------------
        */

        $withdrawal = BalanceWithdrawal::create([
            'employee_id' => $employee->id,
            'amount' => $request->amount,
            'status' => 'pending',
            'note' => $request->note,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan penarikan berhasil dibuat.',
            'data' => $withdrawal,
        ], 201);
    }


    /**
     * Finance menyetujui penarikan.
     */
    public function approve($id)
    {
        DB::beginTransaction();

        try {

            $withdrawal = BalanceWithdrawal::find($id);

            if (!$withdrawal) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan penarikan tidak ditemukan.',
                ], 404);
            }

            if ($withdrawal->status !== 'pending') {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan ini sudah diproses.',
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Ambil saldo dan kunci sementara
            |--------------------------------------------------------------------------
            */

            $balance = EmployeeBalance::where(
                'employee_id',
                $withdrawal->employee_id
            )
            ->lockForUpdate()
            ->first();

            if (!$balance) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Saldo employee tidak ditemukan.',
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | Cek saldo
            |--------------------------------------------------------------------------
            */

            if ($balance->balance < $withdrawal->amount) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi untuk penarikan.',
                    'saldo' => $balance->balance,
                    'dibutuhkan' => $withdrawal->amount,
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Kurangi saldo
            |--------------------------------------------------------------------------
            */

            $balance->balance -= $withdrawal->amount;
            $balance->save();

            /*
            |--------------------------------------------------------------------------
            | Catat transaksi saldo
            |--------------------------------------------------------------------------
            */

            BalanceTransaction::create([
                'employee_id' => $withdrawal->employee_id,
                'payroll_id' => null,
                'type' => 'debit',
                'amount' => $withdrawal->amount,
                'description' => 'Penarikan saldo',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Ubah status pengajuan
            |--------------------------------------------------------------------------
            */

            $withdrawal->update([
                'status' => 'approved',
                'processed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penarikan berhasil disetujui dan saldo telah dikurangi.',
                'data' => [
                    'withdrawal' => $withdrawal,
                    'balance' => $balance,
                ],
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui penarikan: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Finance menolak penarikan.
     */
    public function reject($id)
    {
        $withdrawal = BalanceWithdrawal::find($id);

        if (!$withdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan penarikan tidak ditemukan.',
            ], 404);
        }

        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan ini sudah diproses.',
            ], 422);
        }

        $withdrawal->update([
            'status' => 'rejected',
            'processed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan penarikan ditolak.',
            'data' => $withdrawal,
        ]);
    }
}
