<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
         'employee_id',
         'bulan',
        'tahun',
        'gaji_harian',
        'total_gaji_dasar',

        'bonus_datang_awal',
        

        'total_potongan',
        'total_uang_makan',
        'total_kasbon',
        'total_koreksi',
        'potongan_terlambat',
        'potongan_izin',
        'potongan_cuti',

        'take_home_pay',

        'total_hadir',
        'total_terlambat',
        'total_izin',
        'total_cuti',
        'total_sakit',

        'status',
        'payment_method',

        'finance_id',
        'generated_at',

    ];

    protected $casts = [

        'gaji_harian' => 'decimal:2',
        'total_gaji_dasar' => 'decimal:2',

        'bonus_datang_awal' => 'decimal:2',
        

        'total_potongan' => 'decimal:2',
        'total_kasbon' => 'decimal:2',
        'total_uang_makan' => 'decimal:2',
        'total_koreksi' => 'decimal:2',
         'potongan_terlambat' => 'decimal:2',
    'potongan_izin'      => 'decimal:2',
    'potongan_cuti'      => 'decimal:2',


        'take_home_pay' => 'decimal:2',

        'generated_at' => 'datetime',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi Employee
    |--------------------------------------------------------------------------
    */

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Finance
    |--------------------------------------------------------------------------
    */

    public function finance()
    {
        return $this->belongsTo(Finance::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor (Opsional)
    |--------------------------------------------------------------------------
    */

    public function getPeriodeAttribute()
    {
        return sprintf('%02d/%04d', $this->bulan, $this->tahun);
    }

    public function corrections()
{
    return $this->hasMany(PayrollCorrection::class);
}

public function cashAdvances()
{
    return $this->hasMany(CashAdvance::class);
}
}