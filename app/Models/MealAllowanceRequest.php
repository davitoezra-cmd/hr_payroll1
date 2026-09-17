<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealAllowanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'meal_date',
        'amount',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'meal_date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Karyawan yang mengajukan uang makan
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Admin yang melakukan approval
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}