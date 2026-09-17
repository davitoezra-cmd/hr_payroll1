<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [

            [
                'employee_code' => 'EMP001',
                'name' => 'Admin 1',
                'email' => 'admin1@gembok.com',
                'phone' => '081222222222',
                'password' => Hash::make('admin123'),
               
               
            ],

            [
                'employee_code' => 'EMP002',
                'name' => 'Budi Santoso',
                'email' => 'budi.tech@gembok.com',
                'phone' => '081234567890',
                'password' => Hash::make('budi123'),
                
                
            ],

            [
                'employee_code' => 'EMP003',
                'name' => 'Asep Hidayat',
                'email' => 'asep.tech@gembok.com',
                'phone' => '081234567891',
                'password' => Hash::make('asep123'),
               
               
            ],

            [
                'employee_code' => 'EMP004',
                'name' => 'Dedi Kurniawan',
                'email' => 'dedi.install@gembok.com',
                'phone' => '081234567892',
                'password' => Hash::make('dedi123'),
                
               
            ],
        ];

        foreach ($employees as $employee) {

           Employee::updateOrCreate(
    [
        'employee_code' => $employee['employee_code']
    ],
    $employee
);
        }
    }
}