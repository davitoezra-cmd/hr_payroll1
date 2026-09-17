<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeShiftSchedule extends Model
{
    use HasFactory;

    protected $table = 'employee_shift_schedules';

    protected $fillable = [
        'employee_id',
        'shift_id',
        'work_date',
        'status',
        'notes',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date:Y-m-d',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(WorkShift::class, 'shift_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function attendance()
    {
        return $this->hasOne(Attendance::class, 'employee_shift_schedule_id');
    }
}
