<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BalanceTransaction;
use App\Models\BalanceWithdrawal;
use App\Models\EmployeeBalance;
use Illuminate\Support\Facades\DB;

class BalanceWithdrawalController extends Controller
{
    /**
     * Menampilkan semua pengajuan penarikan.
     */
    public function index()
    {
        $withdrawals = BalanceWithdrawal::with('employee')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $withdrawals,
        ]);
    }

    /**
     * Menyetujui pengajuan penarikan.
     */
    public function approve($id)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Cari dan kunci pengajuan
            |--------------------------------------------------------------------------
            */

            $withdrawal = BalanceWithdrawal::where('id', $id)
                ->lockForUpdate()
                ->first();

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
                    'status' => $withdrawal->status,
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Cari dan kunci saldo employee
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

            

            if ($balance->balance < $withdrawal->amount) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Saldo employee tidak mencukupi.',
                    'saldo' => $balance->balance,
                    'jumlah_penarikan' => $withdrawal->amount,
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
            |
            | Penarikan bukan berasal dari payroll tertentu,
            | jadi payroll_id = null.
            |
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

            /*
            |--------------------------------------------------------------------------
            | Simpan semua perubahan
            |--------------------------------------------------------------------------
            */

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' => true,
                'message' => 'Penarikan berhasil disetujui.',
                'data' => [
                    'withdrawal' => $withdrawal,
                    'balance' => $balance,
                ],
            ], 200);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Batalkan semua perubahan jika terjadi error
            |--------------------------------------------------------------------------
            */

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui penarikan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menolak pengajuan penarikan.
     */
    public function reject($id)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Cari dan kunci pengajuan
            |--------------------------------------------------------------------------
            */

            $withdrawal = BalanceWithdrawal::where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$withdrawal) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan penarikan tidak ditemukan.',
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | Pastikan masih pending
            |--------------------------------------------------------------------------
            */

            if ($withdrawal->status !== 'pending') {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan ini sudah diproses.',
                    'status' => $withdrawal->status,
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Ubah status menjadi rejected
            |--------------------------------------------------------------------------
            */

            $withdrawal->update([
                'status' => 'rejected',
                'processed_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Simpan perubahan
            |--------------------------------------------------------------------------
            */

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan penarikan ditolak.',
                'data' => $withdrawal,
            ], 200);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak pengajuan penarikan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

