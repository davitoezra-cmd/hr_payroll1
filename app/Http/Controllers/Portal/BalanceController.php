<?php

namespace App\Http\Controllers\Portal;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeBalance;
use App\Models\BalanceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    /**
     * Menampilkan saldo karyawan yang sedang login.
     */
    public function myBalance(Request $request)
{
    $employee = auth()->user();

    if (!$employee) {
        return response()->json([
            'success' => false,
            'message' => 'Employee belum login'
        ], 401);
    }

    $balance = EmployeeBalance::firstOrCreate(
        [
            'employee_id' => $employee->id
        ],
        [
            'balance' => 0
        ]
    );

    return response()->json([
        'success' => true,
        'data' => [
            'employee_id' => $employee->id,
            'balance' => $balance->balance,
        ]
    ]);
}
    
    public function index()
    {
        $balances = EmployeeBalance::with('employee')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $balances
        ]);
    }


    /**
     * Menambahkan saldo.
     *
     * Contoh:
     * Payroll Agustus = Rp6.000.000
     *
     * Maka saldo bertambah Rp6.000.000.
     */
    public function credit(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {

            $balance = EmployeeBalance::firstOrCreate(
                [
                    'employee_id' => $request->employee_id
                ],
                [
                    'balance' => 0
                ]
            );

            $balance->balance += $request->amount;
            $balance->save();

            BalanceTransaction::create([
                'employee_id' => $request->employee_id,
                'type' => 'credit',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Penambahan saldo',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Saldo berhasil ditambahkan',
                'data' => [
                    'balance' => $balance->balance
                ]
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan saldo',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Mengurangi saldo / penarikan.
     */
    public function debit(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {

            $balance = EmployeeBalance::where(
                'employee_id',
                $request->employee_id
            )->lockForUpdate()->first();

            if (!$balance) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Saldo karyawan belum tersedia'
                ], 404);
            }

            if ($balance->balance < $request->amount) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi',
                    'saldo' => $balance->balance,
                    'diminta' => $request->amount
                ], 422);
            }

            $balance->balance -= $request->amount;
            $balance->save();

            BalanceTransaction::create([
                'employee_id' => $request->employee_id,
                'type' => 'debit',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Penarikan saldo',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penarikan berhasil',
                'data' => [
                    'balance' => $balance->balance
                ]
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan penarikan',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Menampilkan riwayat transaksi saldo
     * milik employee yang sedang login.
     */
   public function myTransactions()
{
    $employee = auth()->user();

    if (!$employee) {
        return response()->json([
            'success' => false,
            'message' => 'Employee belum login'
        ], 401);
    }

    $transactions = BalanceTransaction::where(
        'employee_id',
        $employee->id
    )
    ->latest()
    ->get();

    return response()->json([
        'success' => true,
        'data' => $transactions
    ]);
}


    /**
     * Menampilkan riwayat transaksi berdasarkan employee.
     * Untuk admin.
     */
    public function transactions($employeeId)
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee tidak ditemukan'
            ], 404);
        }

        $transactions = BalanceTransaction::where(
            'employee_id',
            $employeeId
        )
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
}