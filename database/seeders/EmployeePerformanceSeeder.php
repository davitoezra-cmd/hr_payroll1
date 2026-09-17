<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supervisor;
use App\Models\EmployeeTarget;
use App\Models\EmployeePerformance;

class EmployeePerformanceSeeder extends Seeder
{
    public function run(): void
    {
        $supervisor = Supervisor::first();

        if (!$supervisor) {
            return;
        }

        foreach (EmployeeTarget::all() as $target) {

            $score = rand(70, 100);

            $grade = match (true) {
                $score >= 90 => 'A',
                $score >= 80 => 'B',
                $score >= 70 => 'C',
                $score >= 60 => 'D',
                default => 'E',
            };

            EmployeePerformance::create([
                'employee_id'        => $target->employee_id,
                'employee_target_id' => $target->id,
                'supervisor_id'      => $supervisor->id,
                'score'              => $score,
                'grade'              => $grade,
                'feedback'           => fake()->sentence(12),
            ]);
        }
    }
}