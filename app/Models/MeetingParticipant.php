<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MeetingParticipant extends Model
{
    protected $fillable = [
        'meeting_id',
        'participant_id',
        'participant_type',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function participant(): MorphTo
    {
        return $this->morphTo();
    }
}