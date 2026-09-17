<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeePerformance extends Model
{
     use HasFactory;

    protected $fillable = [
        'employee_id',
        'supervisor_id',
        'employee_target_id',
        'score',
        'grade',
        'feedback',
    ];

    protected $casts = [
        'score' => 'integer',
        'grade' => 'string',
        'feedback' => 'string',
    ];

    public function employeeTarget()
{
    return $this->belongsTo(EmployeeTarget::class);
}

public function employee()
{
    return $this->belongsTo(Employee::class);
}

public function supervisor()
{
    return $this->belongsTo(Supervisor::class);
}


}
