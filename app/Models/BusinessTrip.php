<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessTrip extends Model
{
    use HasFactory;

    protected $fillable = [

        'employee_id',

        'trip_date',

        'destination',

        'purpose',

        'status',

        'approved_by',
        'approved_at',


        'check_in',
        'check_out',

        'check_in_photo',
        'check_out_photo',

        'check_in_latitude',
        'check_in_longitude',

        'check_out_latitude',
        'check_out_longitude',

    ];

    protected $casts = [

        'trip_date' => 'date',

        'approved_at' => 'datetime',

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
}
