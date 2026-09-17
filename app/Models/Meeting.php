<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    protected $fillable = [
        'title',
        'meeting_date',
        'start_time',
        'end_time',
        'location',
        'created_by',
        'agenda',
        'minutes',
        'status',
    ];

    protected $casts = [
        'meeting_date' => 'date',
    ];

    /**
     * User yang membuat rapat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Semua peserta rapat
     */
    public function participants(): HasMany
    {
        return $this->hasMany(MeetingParticipant::class);
    }
}