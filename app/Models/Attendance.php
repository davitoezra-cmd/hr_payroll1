<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'employee_shift_schedule_id',
        'attendance_date',
        'image_selfie',
        'check_in',
        'check_out',
        'scheduled_check_in',
        'scheduled_check_out',
        'late_tolerance_minutes',
        'status',
        'metode',
        'latitude',
        'longitude',
        'checkout_latitude',
        'checkout_longitude',
        'check_in_limit',
        'bonus_didapat',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date:Y-m-d',
            'late_tolerance_minutes' => 'integer',
            'bonus_didapat' => 'boolean',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shiftSchedule()
    {
        return $this->belongsTo(EmployeeShiftSchedule::class, 'employee_shift_schedule_id');
    }
}
