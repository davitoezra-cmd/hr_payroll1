<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CashAdvance;
use App\Models\Employee;

class CashAdvanceSeeder extends Seeder
{
    public function run(): void
    {
        CashAdvance::truncate();

        foreach (Employee::all() as $employee) {

            CashAdvance::create([
                'employee_id' => $employee->id,
                'amount' => rand(20000, 100000),
                'reason' => collect([
                    'Pinjam uang',
                    'Keperluan keluarga',
                    'Biaya kesehatan',
                    'Keperluan mendesak',
                    'Biaya transportasi',
                ])->random(),
                'status' => collect([
                    'approved',
                    'pending',
                    'rejected',
                ])->random(),
                'is_paid' => (bool) rand(0, 1),
                'is_deducted' => (bool) rand(0, 1),
            ]);

        }
    }
}