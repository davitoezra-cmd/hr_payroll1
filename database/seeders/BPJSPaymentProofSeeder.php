<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BPJSPaymentProof;
use App\Models\Finance;
use App\Models\Employee;

class BPJSPaymentProofSeeder extends Seeder
{
    public function run(): void
    {
        $financeId = Finance::first()?->id ?? 1;

        $data = [

            [
                'bpjs_type' => 'kesehatan',
                'period' => '2026-08',
                'document_name' => 'Bukti Pembayaran BPJS Kesehatan Agustus 2026',
                'file_path' => 'bpjs/bpjs_kesehatan_agustus_2026.pdf',
                'notes' => 'Pembayaran BPJS Kesehatan periode Agustus 2026.',
            ],

            [
                'bpjs_type' => 'ketenagakerjaan',
                'period' => '2026-08',
                'document_name' => 'Bukti Pembayaran BPJS Ketenagakerjaan Agustus 2026',
                'file_path' => 'bpjs/bpjs_ketenagakerjaan_agustus_2026.pdf',
                'notes' => 'Pembayaran BPJS Ketenagakerjaan periode Agustus 2026.',
            ],

            [
                'bpjs_type' => 'kesehatan',
                'period' => '2026-07',
                'document_name' => 'Bukti Pembayaran BPJS Kesehatan Juli 2026',
                'file_path' => 'bpjs/bpjs_kesehatan_juli_2026.pdf',
                'notes' => 'Pembayaran BPJS Kesehatan periode Juli 2026.',
            ],

            [
                'bpjs_type' => 'ketenagakerjaan',
                'period' => '2026-07',
                'document_name' => 'Bukti Pembayaran BPJS Ketenagakerjaan Juli 2026',
                'file_path' => 'bpjs/bpjs_ketenagakerjaan_juli_2026.pdf',
                'notes' => 'Pembayaran BPJS Ketenagakerjaan periode Juli 2026.',
            ],

        ];

       $employees = Employee::all();

foreach ($employees as $employee) {

    BPJSPaymentProof::create([
        'employee_id' => $employee->id,
        'finance_id' => 1,

        'bpjs_type' => 'kesehatan',

        'period' => '2026-08',

        'document_name' => 'BPJS Kesehatan Agustus 2026',

        'file_path' => 'bpjs/bpjs_kesehatan_agustus_2026.pdf',

        'notes' => 'Pembayaran BPJS bulan Agustus.'
    ]);

}
    }
}