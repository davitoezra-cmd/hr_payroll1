<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Finance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FinanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Finance::UpdateOrCreate(
            [
                 'email' => 'finance1@gmail.com',
            ],
        [
            'name' => 'Finance 1',

           

            'password' => Hash::make('password'),
        ]);
}
}