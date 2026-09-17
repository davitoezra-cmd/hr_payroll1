<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceQr;
use Illuminate\Support\Str;

class AttendanceQrSeeder extends Seeder
{
    public function run(): void
    {
        AttendanceQr::updateOrCreate(

            [
                'name' => 'Kantor Pusat'
            ],

            [
                'token'       => (string) Str::uuid(),
                'is_active'   => true,
                'expired_at'  => null,
            ]

        );
    }
}