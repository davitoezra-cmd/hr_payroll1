<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TeamMember extends Model
{
    protected $fillable = [
        'team_id',
        'member_id',
        'member_type',
    ];

    /**
     * Tim.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Anggota tim.
     *
     * Bisa:
     * - Supervisor
     * - Employee
     */
    public function member(): MorphTo
    {
        return $this->morphTo();
    }
}