<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class SupervisorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        Supervisor::UpdateOrCreate(
            [
                 'email' => 'supervisor1@gmail.com',
            ],
        [
            'name' => 'Supervisor 1',

           

            'password' => Hash::make('supervisor123'),
        ]);
}
    
}
