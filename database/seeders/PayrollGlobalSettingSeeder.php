<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PayrollGlobalSetting;

class PayrollGlobalSettingSeeder extends Seeder
{
    public function run(): void
    {
        PayrollGlobalSetting::updateOrCreate(
            ['id' => 1],
            [
                'bonus_kedisiplinan' => 100000,
            ]
        );

        $this->command?->info(
            'Payroll global setting berhasil dibuat.'
        );
    }
}