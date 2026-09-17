<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\LeaveRequest;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        LeaveRequest::truncate();

        foreach (Employee::all() as $employee) {

            // Izin
            LeaveRequest::create([
                'employee_id' => $employee->id,
                'type' => 'izin',
                'start_date' => '2026-08-11',
                'end_date' => '2026-08-11',
                'reason' => 'Keperluan keluarga',
                'status' => 'approved',
            ]);

            // Cuti
            LeaveRequest::create([
                'employee_id' => $employee->id,
                'type' => 'cuti',
                'start_date' => '2026-08-12',
                'end_date' => '2026-08-13',
                'reason' => 'Liburan',
                'status' => 'approved',
            ]);
        }
    }
}