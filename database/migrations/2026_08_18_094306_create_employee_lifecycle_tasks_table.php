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
        Schema::create('employee_lifecycle_tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employment_id')
                ->constrained('employee_employments')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('separation_id')
                ->nullable()
                ->constrained('employee_separations')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->enum('phase', [
                'ONBOARDING',
                'OFFBOARDING',
            ]);

            $table->string('task_name');
            $table->text('description')->nullable();

            $table->enum('status', [
                'PENDING',
                'IN_PROGRESS',
                'COMPLETED',
                'SKIPPED',
            ])->default('PENDING');

            $table->date('due_date')->nullable();

            $table->dateTime('completed_at')->nullable();

            $table->foreignId('completed_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('separation_id');
            $table->index('phase');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_lifecycle_tasks');
    }
};
