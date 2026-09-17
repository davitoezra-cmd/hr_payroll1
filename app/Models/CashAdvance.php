<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashAdvance extends Model
{
    use HasFactory;

    protected $fillable = [

        'employee_id',
        'payroll_id',

        'amount',

        'reason',

        'status',

        'approved_by',
        'approved_at',


        'is_paid',
        'paid_at',

    ];

    protected $casts = [

        'amount' => 'decimal:2',

        'approved_at' => 'datetime',

        'paid_at' => 'datetime',

        'is_paid' => 'boolean',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payroll()
{
    return $this->belongsTo(Payroll::class);
}
}
