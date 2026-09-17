<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePayrollSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'gaji_harian',
        'bonus_datang_awal',
        'bonus_kedisiplinan',
        'tarif_lembur',
        'potongan_terlambat',
        'potongan_izin',
        'potongan_cuti',
        'jatah_hari_libur',
        'jatah_cuti',
        'tanggal_gajian',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'gaji_harian' => 'decimal:2',

            'bonus_datang_awal' => 'decimal:2',

            'bonus_kedisiplinan' => 'decimal:2',

            'tarif_lembur' => 'decimal:2',

            'potongan_terlambat' => 'decimal:2',

            'potongan_izin' => 'decimal:2',

            'potongan_cuti' => 'decimal:2',

            'jatah_hari_libur' => 'integer',

            'jatah_cuti' => 'integer',

            'tanggal_gajian' => 'integer',

            'aktif' => 'boolean',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}