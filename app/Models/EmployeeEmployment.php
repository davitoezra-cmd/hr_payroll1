<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeEmployment extends Model
{
    protected $fillable = [
        "employee_id",
        "start_date",
        "end_date",
        "current_status",
        "onboarding_started_at",
        "onboarding_completed_at",
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'onboarding_started_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(EmployeeStatusHistorie::class, "employment_id");
    }

    public function separations(): HasMany
    {
        return $this->hasMany(EmployeeSeparation::class, "employment_id");
    }

    public function lifecycleTasks(): HasMany
    {
        return $this->hasMany(EmployeeLifecycleTask::class, "employment_id");
    }
}
