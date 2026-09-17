<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'finance_id',
        'expense_date',
        'period',
        'category',
        'description',
        'amount',
        'recipient_type',
        'proof_file',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Finance yang membuat transaksi.
     */
    public function finance()
    {
        return $this->belongsTo(Finance::class);
    }

    /**
     * Teknisi/employee yang terkait dengan pengeluaran.
     */
    public function employees()
    {
        return $this->belongsToMany(
            Employee::class,
            'operational_expense_employees',
            'operational_expense_id',
            'employee_id'
        )->withTimestamps();
    }

    /**
     * Cek apakah pengeluaran berlaku untuk semua teknisi.
     */
    public function isForAllEmployees(): bool
    {
        return $this->recipient_type === 'all';
    }

    /**
     * Cek apakah pengeluaran hanya untuk teknisi tertentu.
     */
    public function isForSelectedEmployees(): bool
    {
        return $this->recipient_type === 'selected';
    }

    /**
     * URL bukti transaksi.
     */
    public function getProofUrlAttribute()
    {
        if (!$this->proof_file) {
            return null;
        }

        return asset('storage/' . $this->proof_file);
    }
}