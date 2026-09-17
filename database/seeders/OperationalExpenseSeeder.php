<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OperationalExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('operational_expenses')->insert([
            // =====================================================
            // 1. MAKAN BERSAMA - SEMUA TEKNISI
            // =====================================================
            [
                'finance_id' => null,

                'expense_date' => '2026-08-05',
                'period' => '2026-08',

                'category' => 'makan_bersama',

                'description' => 'Makan bersama seluruh teknisi dalam kegiatan operasional bulanan.',

                'amount' => 750000,

                'recipient_type' => 'all',

                'proof_file' => null,

                'created_at' => $now,
                'updated_at' => $now,
            ],

            // =====================================================
            // 2. UANG MAKAN LEMBUR - SEMUA TEKNISI
            // =====================================================
            [
                'finance_id' => null,

                'expense_date' => '2026-08-10',
                'period' => '2026-08',

                'category' => 'uang_makan_lembur',

                'description' => 'Uang makan untuk teknisi yang melaksanakan pekerjaan lembur.',

                'amount' => 500000,

                'recipient_type' => 'all',

                'proof_file' => null,

                'created_at' => $now,
                'updated_at' => $now,
            ],

            // =====================================================
            // 3. BBM / TRANSPORTASI - TEKNISI TERPILIH
            // =====================================================
            [
                'finance_id' => null,

                'expense_date' => '2026-08-12',
                'period' => '2026-08',

                'category' => 'transportasi',

                'description' => 'Biaya transportasi dan BBM untuk kegiatan operasional teknisi.',

                'amount' => 350000,

                'recipient_type' => 'selected',

                'proof_file' => null,

                'created_at' => $now,
                'updated_at' => $now,
            ],

            // =====================================================
            // 4. PEMBELIAN ALAT KERJA - SEMUA TEKNISI
            // =====================================================
            [
                'finance_id' => null,

                'expense_date' => '2026-08-15',
                'period' => '2026-08',

                'category' => 'alat_kerja',

                'description' => 'Pembelian perlengkapan dan alat kerja untuk mendukung kegiatan teknisi.',

                'amount' => 1250000,

                'recipient_type' => 'all',

                'proof_file' => null,

                'created_at' => $now,
                'updated_at' => $now,
            ],

            // =====================================================
            // 5. KEPERLUAN OPERASIONAL - TEKNISI TERPILIH
            // =====================================================
            [
                'finance_id' => null,

                'expense_date' => '2026-08-18',
                'period' => '2026-08',

                'category' => 'operasional',

                'description' => 'Biaya keperluan operasional untuk beberapa teknisi.',

                'amount' => 425000,

                'recipient_type' => 'selected',

                'proof_file' => null,

                'created_at' => $now,
                'updated_at' => $now,
            ],

            // =====================================================
            // 6. MAKAN BERSAMA - SEMUA TEKNISI
            // =====================================================
            [
                'finance_id' => null,

                'expense_date' => '2026-08-22',
                'period' => '2026-08',

                'category' => 'makan_bersama',

                'description' => 'Konsumsi teknisi untuk kegiatan operasional lapangan.',

                'amount' => 600000,

                'recipient_type' => 'all',

                'proof_file' => null,

                'created_at' => $now,
                'updated_at' => $now,
            ],

            // =====================================================
            // 7. UANG MAKAN LEMBUR - TEKNISI TERPILIH
            // =====================================================
            [
                'finance_id' => null,

                'expense_date' => '2026-08-25',
                'period' => '2026-08',

                'category' => 'uang_makan_lembur',

                'description' => 'Uang makan lembur untuk teknisi yang bertugas di luar jam kerja.',

                'amount' => 300000,

                'recipient_type' => 'selected',

                'proof_file' => null,

                'created_at' => $now,
                'updated_at' => $now,
            ],

            // =====================================================
            // 8. LAINNYA
            // =====================================================
            [
                'finance_id' => null,

                'expense_date' => '2026-08-27',
                'period' => '2026-08',

                'category' => 'lainnya',

                'description' => 'Pengeluaran lain yang berkaitan dengan kebutuhan operasional teknisi.',

                'amount' => 200000,

                'recipient_type' => 'all',

                'proof_file' => null,

                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}