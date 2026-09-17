<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAssignment extends Model
{
    protected $fillable = [
        'assigned_by',
        'team_id',
        'title',
        'description',
        'status',
        'deadline',
        'completed_at',
        'completion_note',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Superadmin/User yang membuat tugas.
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Tim yang menerima tugas.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}