<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollGlobalSetting extends Model
{
    protected $table = 'payroll_global_settings';

    protected $fillable = [
        'bonus_kedisiplinan',
    ];

    protected $casts = [
        'bonus_kedisiplinan' => 'decimal:2',
    ];
}