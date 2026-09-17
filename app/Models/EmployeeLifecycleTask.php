<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLifecycleTask extends Model
{
    protected $fillable = [
        "employment_id",
        "separation_id",
        "phase",
        "task_name",
        "description",
        "status",
        "due_date",
        "completed_at",
        "completed_by",
        "notes",
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function employment():BelongsTo
    {
        return $this->belongsTo(EmployeeEmployment::class, "employment_id");
    }

    public function separation():BelongsTo
    {
        return $this->belongsTo(EmployeeSeparation::class, "separation_id");
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "completed_by");
    }
}
