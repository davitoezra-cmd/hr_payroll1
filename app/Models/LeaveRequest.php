<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
    'employee_id',
    'type',
    'start_date',
    'end_date',
    'reason',
    'attachment',
    'status',
    'approved_by',
    'approved_at',

];

public function employee()
{
    return $this->belongsTo(Employee::class);
}

public function approver()
{
    return $this->belongsTo(User::class, 'approved_by');
}
}
