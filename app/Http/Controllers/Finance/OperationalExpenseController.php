<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\OperationalExpense;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OperationalExpenseController extends Controller
{
    /**
     * WhatsApp Service
     */
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * ================================================================
     * PROOF URL HELPER
     * ================================================================
     *
     * Menambahkan proof_url ke response JSON.
     */
    private function attachProofUrl($expense)
    {
        if (!$expense) {
            return $expense;
        }

        if ($expense instanceof Collection) {
            $expense->each(function ($item) {
                $this->attachProofUrl($item);
            });

            return $expense;
        }

        $expense->proof_url = $expense->proof_file
            ? Storage::disk('public')->url($expense->proof_file)
            : null;

        return $expense;
    }

    /**
     * ================================================================
     * BUILD PESAN WHATSAPP
     * ================================================================
     *
     * Pesan dibuat berdasarkan data OperationalExpense
     * yang sudah disimpan oleh store().
     *
     * WhatsApp hanya mengirim TEXT.
     */
    private function buildExpenseMessage(OperationalExpense $expense): string
    {
        $categoryLabel = ucwords(
            str_replace(
                '_',
                ' ',
                (string) $expense->category
            )
        );

        /**
         * Format tanggal transaksi.
         */
        $expenseDate = '-';

        if (!empty($expense->expense_date)) {
            try {
                $expenseDate = Carbon::parse(
                    $expense->expense_date
                )
                    ->locale('id')
                    ->translatedFormat('d F Y');
            } catch (\Throwable $e) {
                $expenseDate = (string) $expense->expense_date;
            }
        }

        /**
         * Format periode.
         */
        $period = '-';

        if (!empty($expense->period)) {
            try {
                $period = Carbon::createFromFormat(
                    'Y-m',
                    $expense->period
                )
                    ->locale('id')
                    ->translatedFormat('F Y');
            } catch (\Throwable $e) {
                $period = (string) $expense->period;
            }
        }

        /**
         * Format nominal.
         */
        $amount = 'Rp ' . number_format(
            (float) $expense->amount,
            0,
            ',',
            '.'
        );

        /**
         * Jenis penerima.
         */
        $recipientLabel = $expense->recipient_type === 'all'
            ? 'Semua Teknisi'
            : 'Teknisi Terpilih';

        /**
         * Keterangan.
         */
        $description = !empty($expense->description)
            ? $expense->description
            : 'Tidak ada keterangan.';

        /**
         * ============================================================
         * PESAN WHATSAPP
         * ============================================================
         */
        $lines = [
            '*Pemberitahuan Pengeluaran Operasional*',
            '',
            "Kategori: {$categoryLabel}",
            "Tanggal Transaksi: {$expenseDate}",
            "Periode: {$period}",
            "Nominal: {$amount}",
            "Berlaku Untuk: {$recipientLabel}",
            "Keterangan: {$description}",
        ];

        /**
         * Kalau ada bukti transaksi,
         * kirim LINK sebagai text.
         *
         * Tidak mengirim gambar.
         */
        if (!empty($expense->proof_url)) {
            $lines[] = '';
            $lines[] = "Bukti Transaksi:";
            $lines[] = $expense->proof_url;
        }

        return implode("\n", $lines);
    }

    /**
     * ================================================================
     * NOTIFIKASI WHATSAPP
     * ================================================================
     *
     * WhatsApp hanya mengirim TEXT.
     *
     * recipient_type = all
     *      -> semua employee aktif yang memiliki nomor
     *
     * recipient_type = selected
     *      -> employee yang dipilih
     */
    private function notifyEmployees(OperationalExpense $expense): void
    {
        try {
            /**
             * ========================================================
             * TENTUKAN PENERIMA
             * ========================================================
             */
            if ($expense->recipient_type === 'all') {
                $employees = Employee::query()
                    ->where('is_active', true)
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->get();
            } else {
                $employees = $expense->employees()
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->get();
            }

            /**
             * Tidak ada penerima.
             */
            if ($employees->isEmpty()) {
                Log::warning(
                    'Notifikasi WA pengeluaran tidak memiliki penerima.',
                    [
                        'expense_id' => $expense->id,
                        'recipient_type' => $expense->recipient_type,
                    ]
                );

                return;
            }

            /**
             * ========================================================
             * BUILD MESSAGE
             * ========================================================
             *
             * Pesan dibuat SATU KALI berdasarkan transaksi.
             */
            $message = $this->buildExpenseMessage($expense);

            /**
             * ========================================================
             * KIRIM KE SETIAP EMPLOYEE
             * ========================================================
             */
            foreach ($employees as $employee) {
                try {
                    /**
                     * HANYA SEND TEXT.
                     *
                     * Tidak ada sendImage().
                     */
                    $result = $this->whatsapp->send(
                        $employee->phone,
                        $message
                    );

                    /**
                     * Cek response service.
                     */
                    if (!($result['success'] ?? false)) {
                        Log::warning(
                            'Notifikasi WA pengeluaran operasional gagal terkirim.',
                            [
                                'expense_id' => $expense->id,
                                'employee_id' => $employee->id,
                                'phone' => $employee->phone,
                                'result' => $result,
                            ]
                        );

                        continue;
                    }

                    Log::info(
                        'Notifikasi WA pengeluaran operasional berhasil dikirim.',
                        [
                            'expense_id' => $expense->id,
                            'employee_id' => $employee->id,
                            'phone' => $employee->phone,
                        ]
                    );
                } catch (\Throwable $e) {
                    /**
                     * Kalau satu nomor gagal,
                     * nomor berikutnya tetap dikirim.
                     */
                    Log::error(
                        'Error mengirim WA ke employee.',
                        [
                            'expense_id' => $expense->id,
                            'employee_id' => $employee->id,
                            'phone' => $employee->phone,
                            'error' => $e->getMessage(),
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            /**
             * Error WhatsApp tidak boleh membatalkan transaksi.
             */
            Log::error(
                'Gagal menjalankan notifikasi WhatsApp pengeluaran operasional.',
                [
                    'expense_id' => $expense->id ?? null,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * ================================================================
     * INDEX
     * ================================================================
     */
    public function index(Request $request)
    {
        try {
            $query = OperationalExpense::with([
                'finance',
                'employees',
            ])
                ->latest('expense_date');

            /**
             * Filter periode.
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
             * Filter penerima.
             */
            if ($request->filled('recipient_type')) {
                $query->where(
                    'recipient_type',
                    $request->recipient_type
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

            $data = $query->get();

            /**
             * Tambahkan proof_url.
             */
            $this->attachProofUrl($data);

            /**
             * Total pengeluaran.
             */
            $totalAmount = $data->sum('amount');

            return response()->json([
                'success' => true,
                'message' => 'Data pengeluaran operasional berhasil diambil.',
                'count' => $data->count(),
                'total_amount' => $totalAmount,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Gagal mengambil data pengeluaran operasional.',
                [
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pengeluaran operasional.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ================================================================
     * SHOW
     * ================================================================
     */
    public function show($id)
    {
        try {
            $expense = OperationalExpense::with([
                'finance',
                'employees',
            ])->find($id);

            if (!$expense) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pengeluaran tidak ditemukan.',
                ], 404);
            }

            /**
             * Tambahkan URL bukti.
             */
            $this->attachProofUrl($expense);

            return response()->json([
                'success' => true,
                'data' => $expense,
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Gagal mengambil detail pengeluaran operasional.',
                [
                    'expense_id' => $id,
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail pengeluaran.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ================================================================
     * STORE
     * ================================================================
     */
    public function store(Request $request)
    {
        /**
         * ============================================================
         * VALIDASI
         * ============================================================
         */
        $validated = $request->validate([
            'expense_date' => [
                'required',
                'date',
            ],

            'period' => [
                'required',
                'date_format:Y-m',
            ],

            'category' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'recipient_type' => [
                'required',
                Rule::in([
                    'all',
                    'selected',
                ]),
            ],

            'employee_ids' => [
                'nullable',
                'array',
            ],

            'employee_ids.*' => [
                'integer',
                'exists:employees,id',
            ],

            'proof_file' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
        ]);

        /**
         * ============================================================
         * VALIDASI RECIPIENT
         * ============================================================
         *
         * Kalau selected harus ada employee.
         */
        if (
            $validated['recipient_type'] === 'selected'
            && empty($validated['employee_ids'])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal satu employee untuk pengeluaran yang ditujukan kepada beberapa teknisi.',
                'errors' => [
                    'employee_ids' => [
                        'Employee wajib dipilih.',
                    ],
                ],
            ], 422);
        }

        /**
         * Kalau all, employee_ids tidak diperlukan.
         */
        if ($validated['recipient_type'] === 'all') {
            $validated['employee_ids'] = [];
        }

        /**
         * Deklarasikan di luar try
         * supaya bisa digunakan di catch.
         */
        $proofFile = null;

        try {
            DB::beginTransaction();

            /**
             * ========================================================
             * FINANCE LOGIN
             * ========================================================
             */
            $finance = auth()->user();

            if (!$finance) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Finance belum login.',
                ], 401);
            }

            /**
             * ========================================================
             * UPLOAD BUKTI
             * ========================================================
             */
            if ($request->hasFile('proof_file')) {
                $proofFile = $request
                    ->file('proof_file')
                    ->store(
                        'operational-expenses',
                        'public'
                    );
            }

            /**
             * ========================================================
             * CREATE EXPENSE
             * ========================================================
             *
             * Data di sini adalah sumber utama
             * untuk pesan WhatsApp.
             */
            $expense = OperationalExpense::create([
                'finance_id' => $finance->id,
                'expense_date' => $validated['expense_date'],
                'period' => $validated['period'],
                'category' => $validated['category'],
                'description' => $validated['description'] ?? null,
                'amount' => $validated['amount'],
                'recipient_type' => $validated['recipient_type'],
                'proof_file' => $proofFile,
            ]);

            /**
             * ========================================================
             * SIMPAN EMPLOYEE
             * ========================================================
             */
            if (
                $validated['recipient_type'] === 'selected'
                && !empty($validated['employee_ids'])
            ) {
                $expense->employees()->sync(
                    $validated['employee_ids']
                );
            }

            /**
             * ========================================================
             * COMMIT
             * ========================================================
             */
            DB::commit();

            /**
             * ========================================================
             * LOAD ULANG RELASI
             * ========================================================
             */
            $expense->load([
                'finance',
                'employees',
            ]);

            /**
             * Tambahkan proof_url.
             */
            $this->attachProofUrl($expense);

            /**
             * ========================================================
             * KIRIM WHATSAPP
             * ========================================================
             *
             * WA dikirim setelah database berhasil.
             *
             * HANYA TEXT.
             */
            $this->notifyEmployees($expense);

            /**
             * ========================================================
             * RESPONSE
             * ========================================================
             */
            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran operasional berhasil ditambahkan.',
                'data' => $expense,
            ], 201);
        } catch (\Throwable $e) {
            /**
             * ========================================================
             * ROLLBACK
             * ========================================================
             */
            DB::rollBack();

            /**
             * Hapus file jika database gagal.
             */
            if (!empty($proofFile)) {
                Storage::disk('public')->delete(
                    $proofFile
                );
            }

            Log::error(
                'Gagal menambahkan pengeluaran operasional.',
                [
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pengeluaran operasional.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ================================================================
     * UPDATE
     * ================================================================
     */
    public function update(Request $request, $id)
    {
        $expense = OperationalExpense::find($id);

        if (!$expense) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengeluaran tidak ditemukan.',
            ], 404);
        }

        /**
         * Validasi.
         */
        $validated = $request->validate([
            'expense_date' => [
                'required',
                'date',
            ],

            'period' => [
                'required',
                'date_format:Y-m',
            ],

            'category' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'recipient_type' => [
                'required',
                Rule::in([
                    'all',
                    'selected',
                ]),
            ],

            'employee_ids' => [
                'nullable',
                'array',
            ],

            'employee_ids.*' => [
                'integer',
                'exists:employees,id',
            ],

            'proof_file' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
        ]);

        /**
         * Kalau selected harus ada employee.
         */
        if (
            $validated['recipient_type'] === 'selected'
            && empty($validated['employee_ids'])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal satu employee.',
                'errors' => [
                    'employee_ids' => [
                        'Employee wajib dipilih.',
                    ],
                ],
            ], 422);
        }

        /**
         * Kalau all kosongkan employee_ids.
         */
        if ($validated['recipient_type'] === 'all') {
            $validated['employee_ids'] = [];
        }

        /**
         * File baru.
         */
        $newProofFile = null;

        try {
            DB::beginTransaction();

            /**
             * File lama.
             */
            $oldProofFile = $expense->proof_file;

            /**
             * Upload file baru.
             */
            if ($request->hasFile('proof_file')) {
                $newProofFile = $request
                    ->file('proof_file')
                    ->store(
                        'operational-expenses',
                        'public'
                    );
            }

            /**
             * Update expense.
             */
            $expense->update([
                'expense_date' => $validated['expense_date'],
                'period' => $validated['period'],
                'category' => $validated['category'],
                'description' => $validated['description'] ?? null,
                'amount' => $validated['amount'],
                'recipient_type' => $validated['recipient_type'],
                'proof_file' => $newProofFile ?: $oldProofFile,
            ]);

            /**
             * Update employee.
             */
            if ($validated['recipient_type'] === 'all') {
                $expense->employees()->detach();
            } else {
                $expense->employees()->sync(
                    $validated['employee_ids']
                );
            }

            DB::commit();

            /**
             * Hapus bukti lama setelah update berhasil.
             */
            if (
                $newProofFile
                && $oldProofFile
                && $oldProofFile !== $newProofFile
            ) {
                Storage::disk('public')->delete(
                    $oldProofFile
                );
            }

            /**
             * Reload relasi.
             */
            $expense->load([
                'finance',
                'employees',
            ]);

            /**
             * Tambahkan proof_url.
             */
            $this->attachProofUrl($expense);

            /**
             * Catatan:
             *
             * UPDATE tidak mengirim WA.
             *
             * Jadi WhatsApp hanya dikirim ketika
             * transaksi baru dibuat melalui store().
             */

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran operasional berhasil diperbarui.',
                'data' => $expense,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            /**
             * Hapus file baru kalau update gagal.
             */
            if (!empty($newProofFile)) {
                Storage::disk('public')->delete(
                    $newProofFile
                );
            }

            Log::error(
                'Gagal memperbarui pengeluaran operasional.',
                [
                    'expense_id' => $id,
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pengeluaran operasional.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ================================================================
     * DESTROY
     * ================================================================
     */
    public function destroy($id)
    {
        $expense = OperationalExpense::find($id);

        if (!$expense) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengeluaran tidak ditemukan.',
            ], 404);
        }

        try {
            DB::beginTransaction();

            /**
             * Simpan path bukti.
             */
            $proofFile = $expense->proof_file;

            /**
             * Hapus relasi employee.
             */
            $expense->employees()->detach();

            /**
             * Hapus expense.
             */
            $expense->delete();

            DB::commit();

            /**
             * Hapus file fisik.
             */
            if (!empty($proofFile)) {
                Storage::disk('public')->delete(
                    $proofFile
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran operasional berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error(
                'Gagal menghapus pengeluaran operasional.',
                [
                    'expense_id' => $id,
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengeluaran operasional.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ================================================================
     * EMPLOYEES
     * ================================================================
     */
    public function employees()
    {
        try {
            $employees = Employee::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'employee_code',
                    'name',
                    'phone',
                ]);

            return response()->json([
                'success' => true,
                'data' => $employees,
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Gagal mengambil daftar employee.',
                [
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar employee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ================================================================
     * SUMMARY
     * ================================================================
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
            $query = OperationalExpense::where(
                'period',
                $request->period
            );

            /**
             * Total.
             */
            $total = $query->sum('amount');

            /**
             * Jumlah transaksi.
             */
            $count = $query->count();

            /**
             * Total berdasarkan kategori.
             */
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
                'by_category' => $byCategory,
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Gagal mengambil ringkasan pengeluaran.',
                [
                    'period' => $request->period,
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil ringkasan pengeluaran.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}