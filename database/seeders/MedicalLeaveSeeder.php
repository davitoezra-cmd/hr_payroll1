<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\MedicalLeave;

class MedicalLeaveSeeder extends Seeder
{
    public function run(): void
    {
        MedicalLeave::truncate();

        $reasons = [
            'Demam',
            'Flu',
            'Batuk',
            'Sakit kepala',
            'Sakit perut',
            'Tifus',
            'Cedera ringan',
        ];

        foreach (Employee::all() as $employee) {

            MedicalLeave::create([
                'employee_id' => $employee->id,
                'sick_date' => '2026-08-14',
                'reason' => $reasons[array_rand($reasons)],
                'status' => collect([
                    'approved',
                    'pending',
                    'rejected',
                ])->random(),
            ]);

        }
    }
}