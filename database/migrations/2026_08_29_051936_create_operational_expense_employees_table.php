<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operational_expense_employees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('operational_expense_id')
                ->constrained('operational_expenses')
                ->cascadeOnDelete();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->timestamps();

            // Satu employee tidak boleh tercatat
            // dua kali dalam transaksi yang sama
            $table->unique([
                'operational_expense_id',
                'employee_id',
            ], 'operational_expense_employee_unique');

            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_expense_employees');
    }
};