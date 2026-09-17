<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            EmployeeSeeder::class,
            SuperAdminSeeder::class,
            FinanceSeeder::class,
            SupervisorSeeder::class,

            // Shift harus tersedia sebelum mapping dan attendance.
            WorkShiftSeeder::class,
            EmployeeShiftScheduleSeeder::class,
            AttendanceSeeder::class,
            AttendanceQrSeeder::class,

            
            MedicalLeaveSeeder::class,
            LeaveRequestSeeder::class,
            CashAdvanceSeeder::class,
            EmployeePayrollSettingSeeder::class,
            EmployeeTargetSeeder::class,
            BPJSPaymentProofSeeder::class,
            EmployeePerformanceSeeder::class,
            TaskAssignmentSeeder::class,
            TeamSeeder::class,
            MeetingSeeder::class,
            InventorySeeder::class,
            PayrollGlobalSettingSeeder::class,
            OperationalExpenseSeeder::class,
            AdminSeeder::class,
            CompanyPostSeeder::class,
            MealAllowanceRequestSeeder::class,
        ]);
    }
}
