<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'jam_masuk',
        'jam_pulang',
        'late_tolerance_minutes',
        'batas_telat',
        'mulai_bonus_datang',
        'mulai_lembur',
        'lintas_hari',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'late_tolerance_minutes' => 'integer',
            'lintas_hari' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function schedules()
    {
        return $this->hasMany(EmployeeShiftSchedule::class, 'shift_id');
    }
}
