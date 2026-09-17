<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'supervisor_id',
        'title',
        'description',
        'category',
        'target_value',
        'current_value',
        'progress_percent',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress_percent' => 'decimal:2',
    ];

    /**
     * Karyawan yang memiliki target.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Supervisor yang membuat target.
     */
    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class);
    }

   public function employeePerformance()
{
    return $this->hasOne(EmployeePerformance::class);
}
    
}