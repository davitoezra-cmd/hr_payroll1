<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollCorrection extends Model
{
    use HasFactory;

    protected $fillable = [

        'payroll_id',

        'employee_id',

        'finance_id',

        'type',

        'amount',

        'reason',

    ];

    protected $casts = [

        'amount' => 'decimal:2',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function finance()
    {
        return $this->belongsTo(Finance::class);
    }
}