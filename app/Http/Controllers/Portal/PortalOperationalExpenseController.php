<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\OperationalExpense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PortalOperationalExpenseController extends Controller
{
    /**
     * ================================================================
     * INDEX
     * ================================================================
     *
     * Mengambil pengeluaran operasional yang berlaku
     * untuk user/teknisi yang sedang login.
     *
     * Yang ditampilkan:
     * - recipient_type = all
     * - recipient_type = selected dan user terdapat di pivot
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Portal belum login.',
                ], 401);
            }

            /**
             * Ambil employee_id dari user.
             *
             * Sesuaikan jika struktur project Anda
             * menggunakan relasi user -> employee.
             */
            $employeeId = $user->employee_id ?? $user->id;

            $query = OperationalExpense::query()
                ->with([
                    'finance',
                ])
                ->where(function ($q) use ($employeeId) {

                    /**
                     * Pengeluaran untuk semua teknisi.
                     */
                    $q->where(
                        'recipient_type',
                        'all'
                    )

                    /**
                     * ATAU pengeluaran untuk teknisi tertentu.
                     */
                    ->orWhere(function ($q) use ($employeeId) {

                        $q->where(
                            'recipient_type',
                            'selected'
                        )->whereHas(
                            'employees',
                            function ($employeeQuery) use ($employeeId) {
                                $employeeQuery->where(
                                    'employees.id',
                                    $employeeId
                                );
                            }
                        );
                    });
                });

            /**
             * Filter periode.
             *
             * Contoh:
             * /api/portal/operational-expenses?period=2026-08
             */
            if ($request->filled('period')) {
                $query->where(
                    'period',
                    $request->period
                );
            }

            /**
             * Filter kategori.
             */
            if ($request->filled('category')) {
                $query->where(
                    'category',
                    $request->category
                );
            }

            /**
             * Filter tanggal.
             */
            if ($request->filled('expense_date')) {
                $query->whereDate(
                    'expense_date',
                    $request->expense_date
                );
            }

            $expenses = $query
                ->latest('expense_date')
                ->latest('id')
                ->get();

            /**
             * Tambahkan proof_url.
             */
            $expenses->each(function ($expense) {

                $expense->proof_url = $expense->proof_file
                    ? Storage::disk('public')->url(
                        $expense->proof_file
                    )
                    : null;

                /**
                 * Format tanggal untuk frontend.
                 */
                $expense->expense_date_formatted = null;

                if (!empty($expense->expense_date)) {
                    try {
                        $expense->expense_date_formatted =
                            Carbon::parse(
                                $expense->expense_date
                            )
                            ->locale('id')
                            ->translatedFormat(
                                'd F Y'
                            );
                    } catch (\Throwable $e) {
                        $expense->expense_date_formatted =
                            $expense->expense_date;
                    }
                }

                /**
                 * Format periode.
                 */
                $expense->period_formatted = null;

                if (!empty($expense->period)) {
                    try {
                        $expense->period_formatted =
                            Carbon::createFromFormat(
                                'Y-m',
                                $expense->period
                            )
                            ->locale('id')
                            ->translatedFormat(
                                'F Y'
                            );
                    } catch (\Throwable $e) {
                        $expense->period_formatted =
                            $expense->period;
                    }
                }

                /**
                 * Format nominal.
                 */
                $expense->amount_formatted =
                    'Rp ' . number_format(
                        (float) $expense->amount,
                        0,
                        ',',
                        '.'
                    );

                /**
                 * Label penerima.
                 */
                $expense->recipient_label =
                    $expense->recipient_type === 'all'
                        ? 'Semua Teknisi'
                        : 'Teknisi Terpilih';
            });

            return response()->json([
                'success' => true,
                'message' =>
                    'Data pengeluaran operasional berhasil diambil.',
                'count' => $expenses->count(),
                'data' => $expenses,
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Portal gagal mengambil pengeluaran operasional.',
                [
                    'error' => $e->getMessage(),
                    'user_id' => auth()->id(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Gagal mengambil pengeluaran operasional.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ================================================================
     * SHOW
     * ================================================================
     *
     * Mengambil detail satu pengeluaran yang memang
     * berhak dilihat oleh user Portal.
     */
    public function show($id)
    {
        try {

            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Portal belum login.',
                ], 401);
            }

            $employeeId = $user->employee_id ?? $user->id;

            $expense = OperationalExpense::query()
                ->with([
                    'finance',
                ])
                ->where('id', $id)
                ->where(function ($q) use ($employeeId) {

                    /**
                     * Berlaku untuk semua.
                     */
                    $q->where(
                        'recipient_type',
                        'all'
                    )

                    /**
                     * Atau dipilih khusus.
                     */
                    ->orWhere(function ($q) use ($employeeId) {

                        $q->where(
                            'recipient_type',
                            'selected'
                        )->whereHas(
                            'employees',
                            function ($employeeQuery) use ($employeeId) {
                                $employeeQuery->where(
                                    'employees.id',
                                    $employeeId
                                );
                            }
                        );
                    });
                })
                ->first();

            if (!$expense) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Pengeluaran tidak ditemukan atau Anda tidak memiliki akses.',
                ], 404);
            }

            /**
             * Proof URL.
             */
            $expense->proof_url = $expense->proof_file
                ? Storage::disk('public')->url(
                    $expense->proof_file
                )
                : null;

            /**
             * Format tanggal.
             */
            if (!empty($expense->expense_date)) {
                try {
                    $expense->expense_date_formatted =
                        Carbon::parse(
                            $expense->expense_date
                        )
                        ->locale('id')
                        ->translatedFormat(
                            'd F Y'
                        );
                } catch (\Throwable $e) {
                    $expense->expense_date_formatted =
                        $expense->expense_date;
                }
            }

            /**
             * Format periode.
             */
            if (!empty($expense->period)) {
                try {
                    $expense->period_formatted =
                        Carbon::createFromFormat(
                            'Y-m',
                            $expense->period
                        )
                        ->locale('id')
                        ->translatedFormat(
                            'F Y'
                        );
                } catch (\Throwable $e) {
                    $expense->period_formatted =
                        $expense->period;
                }
            }

            /**
             * Format nominal.
             */
            $expense->amount_formatted =
                'Rp ' . number_format(
                    (float) $expense->amount,
                    0,
                    ',',
                    '.'
                );

            $expense->recipient_label =
                $expense->recipient_type === 'all'
                    ? 'Semua Teknisi'
                    : 'Teknisi Terpilih';

            return response()->json([
                'success' => true,
                'data' => $expense,
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Portal gagal mengambil detail pengeluaran operasional.',
                [
                    'expense_id' => $id,
                    'user_id' => auth()->id(),
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Gagal mengambil detail pengeluaran operasional.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ================================================================
     * SUMMARY
     * ================================================================
     *
     * Ringkasan pengeluaran yang berlaku untuk Portal.
     */
    public function summary(Request $request)
    {
        $request->validate([
            'period' => [
                'required',
                'date_format:Y-m',
            ],
        ]);

        try {

            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Portal belum login.',
                ], 401);
            }

            $employeeId = $user->employee_id ?? $user->id;

            $query = OperationalExpense::query()
                ->where(
                    'period',
                    $request->period
                )
                ->where(function ($q) use ($employeeId) {

                    $q->where(
                        'recipient_type',
                        'all'
                    )
                    ->orWhere(function ($q) use ($employeeId) {

                        $q->where(
                            'recipient_type',
                            'selected'
                        )->whereHas(
                            'employees',
                            function ($employeeQuery) use ($employeeId) {
                                $employeeQuery->where(
                                    'employees.id',
                                    $employeeId
                                );
                            }
                        );
                    });
                });

            $total = $query->sum('amount');

            $count = $query->count();

            $byCategory = $query
                ->selectRaw(
                    'category, SUM(amount) as total'
                )
                ->groupBy('category')
                ->orderByDesc('total')
                ->get();

            return response()->json([
                'success' => true,
                'period' => $request->period,
                'count' => $count,
                'total_amount' => $total,
                'total_amount_formatted' =>
                    'Rp ' . number_format(
                        (float) $total,
                        0,
                        ',',
                        '.'
                    ),
                'by_category' => $byCategory,
            ]);

        } catch (\Throwable $e) {

            Log::error(
                'Portal gagal mengambil summary pengeluaran.',
                [
                    'period' => $request->period,
                    'user_id' => auth()->id(),
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Gagal mengambil ringkasan pengeluaran.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}