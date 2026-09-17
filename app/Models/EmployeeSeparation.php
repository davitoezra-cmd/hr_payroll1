<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class EmployeeSeparation extends Model
{
    protected $fillable = [
        "employment_id",
        "separation_type",
        "reason",
        "notice_date",
        "last_working_date",
        "effective_date",
        "process_status",
        "notes",
        "submitted_at",
        "approved_by",
        "approved_at",
        "processed_by",
        "processed_at",
        "offboarding_started_at",
        "offboarding_completed_at",
    ];

    protected function casts(): array
    {
        return [
            'notice_date' => 'date',
            'last_working_date' => 'date',
            'effective_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'processed_at' => 'datetime',
            'offboarding_started_at' => 'datetime',
            'offboarding_completed_at' => 'datetime',
        ];
    }

    public function employment():BelongsTo
    {
        return $this->belongsTo(EmployeeEmployment::class, "employment_id");
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "approved_by");
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "processed_by");
    }

    public function lifecycleTasks(): HasMany
    {
        return $this->hasMany(EmployeeLifecycleTask::class, "separation_id");
    }
}
