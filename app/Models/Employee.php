<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Sanctum\HasApiTokens;
use App\Models\MedicalLeave;
use App\Models\LeaveRequest;


class Employee extends Authenticatable
{
    use Notifiable;
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'employee_code',
        'name',
        'email',
        'nama_bank',
        'no_rekening',
        'nama_rekening',
        'phone',
        'password',

        'is_active',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke data absensi.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function shiftSchedules(): HasMany
    {
        return $this->hasMany(EmployeeShiftSchedule::class);
    }


    public function medicalLeaves()
{
    return $this->hasMany(MedicalLeave::class);
}

    /**
 * Leave Request
 */
public function leaveRequests()
{
    return $this->hasMany(LeaveRequest::class);
}

public function cashAdvances()
{
    return $this->hasMany(CashAdvance::class);
}

    /**
     * Relasi ke data dinas luar.
     */
    public function businessTrips()
    {
        return $this->hasMany(BusinessTrip::class);
    }

    public function payrollSetting()
{
    return $this->hasOne(EmployeePayrollSetting::class);
}

    public function payrollCorrections()
{
    return $this->hasMany(PayrollCorrection::class);
}

    public function targets()
    {
        return $this->hasMany(EmployeeTarget::class);
    }

    public function bpjsPaymentProofs()
{
    return $this->hasMany(BPJSPaymentProof::class);
}

public function balance(): HasOne
{
    return $this->hasOne(EmployeeBalance::class);
}

public function balanceTransactions(): HasMany
{
    return $this->hasMany(BalanceTransaction::class);
}

public function balanceWithdrawals()
{
    return $this->hasMany(BalanceWithdrawal::class);
}
 public function faceTemplate(): HasOne
    {
        return $this->hasOne(EmployeeFaceTemplate::class);
    }

public function meetingParticipants(): MorphMany
{
    return $this->morphMany(
        MeetingParticipant::class,
        'participant'
    );
}

public function employments()
{
    return $this->hasMany(EmployeeEmployment::class);
}

public function currentEmployment(): HasOne
{
    return $this->hasOne(EmployeeEmployment::class)->latestOfMany();
}

public function operationalExpenses()
{
    return $this->belongsToMany(
        OperationalExpense::class,
        'operational_expense_employees',
        'employee_id',
        'operational_expense_id'
    )->withTimestamps();
}
}

