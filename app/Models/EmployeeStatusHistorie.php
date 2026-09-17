<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;


class EmployeeStatusHistorie extends Model
{
    protected $fillable = [
        "employment_id",
        "status",
        "effective_from",
        "effective_to",
        "reason",
        "changed_by",
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'datetime',
            'effective_to' => 'datetime',
        ];
    }

    public function employment():BelongsTo
    {
        return $this->belongsTo(EmployeeEmployment::class, "employment_id");
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "changed_by");
    }
}
