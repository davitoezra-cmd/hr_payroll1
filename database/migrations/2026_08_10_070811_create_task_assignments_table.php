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
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assigned_by')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Tim yang menerima tugas
            |--------------------------------------------------------------------------
            */
            $table->foreignId('team_id')
                ->constrained('teams')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Informasi tugas
            |--------------------------------------------------------------------------
            */
            $table->string('title');

            $table->text('description')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Status tugas
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'cancelled',
            ])->default('pending');
            $table->dateTime('deadline')->nullable();

            $table->dateTime('completed_at')->nullable();

            $table->text('completion_note')->nullable();

            $table->timestamps();
            $table->index('assigned_by');
            $table->index('team_id');
            $table->index('status');
            $table->index('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
    }
};