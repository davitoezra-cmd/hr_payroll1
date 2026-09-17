<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balance_transactions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();
            
            $table->foreignId('payroll_id')
                    ->nullable()
                    ->constrained('payrolls')
                    ->nullOnDelete();
            

            $table->enum('type', [
                'credit',
                'debit'
            ]);

            $table->decimal('amount', 15, 2);

            $table->string('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_transactions');
    }
};