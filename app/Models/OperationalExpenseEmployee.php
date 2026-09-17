<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalExpenseEmployee extends Model
{
    use HasFactory;

    protected $table = 'operational_expense_employees';

    protected $fillable = [
        'operational_expense_id',
        'employee_id',
    ];

    /**
     * Transaksi pengeluaran.
     */
    public function operationalExpense()
    {
        return $this->belongsTo(
            OperationalExpense::class,
            'operational_expense_id'
        );
    }

    /**
     * Employee/teknisi.
     */
    public function employee()
    {
        return $this->belongsTo(
            Employee::class,
            'employee_id'
        );
    }
}