<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Finance extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function payrollCorrections()
{
    return $this->hasMany(PayrollCorrection::class);
}

    public function faceTemplate(): HasOne
    {
        return $this->hasOne(FinanceFaceTemplate::class);
    }

    public function meetingParticipants(): MorphMany
{
    return $this->morphMany(
        MeetingParticipant::class,
        'participant'
    );
}

public function operationalExpenses()
{
    return $this->hasMany(
        OperationalExpense::class,
        'finance_id'
    );
}
}
