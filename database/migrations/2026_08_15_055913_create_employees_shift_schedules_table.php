<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_shift_schedules', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table
                ->foreignId('shift_id')
                ->nullable()
                ->constrained('work_shifts')
                ->restrictOnDelete();

            $table->date('work_date');

            $table->enum('status', [
                'work',
                'off',
            ])->default('work');

            $table->string('notes', 255)->nullable();

            $table
                ->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Satu employee hanya boleh memiliki satu jadwal
            // pada tanggal yang sama.
            $table->unique(
                ['employee_id', 'work_date'],
                'employee_shift_schedules_employee_date_unique'
            );

            $table->index('work_date');
            $table->index('shift_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_shift_schedules');
    }
};
