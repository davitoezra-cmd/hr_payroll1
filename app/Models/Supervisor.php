<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Supervisor extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function targets()
    {
        return $this->hasMany(EmployeeTarget::class);
    }

    public function faceTemplate(): HasOne
    {
        return $this->hasOne(SupervisorFaceTemplate::class);
    }

    public function meetingParticipants(): MorphMany
{
    return $this->morphMany(
        MeetingParticipant::class,
        'participant'
    );
}
}
