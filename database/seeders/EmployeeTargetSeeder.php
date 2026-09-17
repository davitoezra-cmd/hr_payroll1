<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeTarget;
use App\Models\Employee;
use App\Models\Supervisor;

class EmployeeTargetSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();

        if ($employees->isEmpty()) {
            $this->command->warn('Tidak ada data employee.');
            return;
        }

        // Ambil supervisor pertama
        $supervisorId = Supervisor::first()?->id ?? 1;

        foreach ($employees as $index => $employee) {

    EmployeeTarget::create([
        'employee_id' => $employee->id,
        'supervisor_id' => $supervisorId,

        'title' => match ($index % 5) {
            0 => 'Target Servis Bulanan',
            1 => 'Target Instalasi',
            2 => 'Target Maintenance',
            3 => 'Target Perbaikan',
            default => 'Target Survey Lapangan',
        },

        'description' => match ($index % 5) {
            0 => 'Menyelesaikan seluruh pekerjaan servis sesuai jadwal.',
            1 => 'Menyelesaikan instalasi perangkat sesuai target.',
            2 => 'Melakukan maintenance rutin kepada pelanggan.',
            3 => 'Menyelesaikan pekerjaan perbaikan dengan kualitas terbaik.',
            default => 'Melakukan survey lokasi pelanggan sesuai jadwal.',
        },

        'category' => match ($index % 5) {
            0 => 'Servis',
            1 => 'Instalasi',
            2 => 'Maintenance',
            3 => 'Perbaikan',
            default => 'Survey',
        },

        'target_value' => match ($index % 5) {
            0 => 25,
            1 => 15,
            2 => 30,
            3 => 20,
            default => 10,
        },

        'current_value' => match ($index % 3) {
            0 => 25,
            1 => 12,
            default => 5,
        },

        'progress_percent' => match ($index % 3) {
            0 => 100,
            1 => 80,
            default => 50,
        },

        'start_date' => '2026-08-01',
        'end_date' => '2026-08-31',

        'status' => match ($index % 3) {
            0 => 'completed',
            1 => 'ongoing',
            default => 'not_achieved',
        },

        'notes' => 'Target bulanan teknisi yang ditetapkan supervisor.',
    ]);
}
    }
}