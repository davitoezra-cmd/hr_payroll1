<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalLeave extends Model
{
    use HasFactory;

    protected $fillable = [

        'employee_id',

        'sick_date',

        'reason',

        'doctor_note',

        'status',

        'approved_by',
        'approved_at',


    ];

    protected $casts = [

        'sick_date' => 'date',

        'approved_at' => 'datetime',

    ];

    /**
     * Relasi ke Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relasi ke Admin (User)
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
