<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\MedicalLeave;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [

        'name',
        'email',
        'password',
        'phone',

    ];

    protected $hidden = [

        'password',
        'remember_token',

    ];

    protected $casts = [

        'password' => 'hashed',

    ];

    /**
 * Medical Leave yang disetujui admin
 */
public function approvedMedicalLeaves()
{
    return $this->hasMany(MedicalLeave::class, 'approved_by');
}

/**
 * Leave Request yang di-approve admin
 */
public function approvedLeaveRequests()
{
    return $this->hasMany(LeaveRequest::class, 'approved_by');
}

/**
 * Pengajuan lembur yang disetujui admin.
 */
public function approvedOvertimeRequests()
{
    return $this->hasMany(OvertimeRequest::class, 'approved_by');
}

public function approvedCashAdvances()
{
    return $this->hasMany(CashAdvance::class, 'approved_by');
}

public function approvedBusinessTrips()
{
    return $this->hasMany(BusinessTrip::class, 'approved_by');
}

    public function faceTemplate(): HasOne
    {
        return $this->hasOne(UserFaceTemplate::class);
    }
}