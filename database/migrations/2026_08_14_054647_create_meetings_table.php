<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            $table->date('meeting_date');

            $table->time('start_time');

            $table->time('end_time')->nullable();

            $table->string('location')->nullable();

            // User / Superadmin yang membuat rapat
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('agenda')->nullable();

            $table->text('minutes')->nullable();

            $table->enum('status', [
                'scheduled',
                'completed',
                'cancelled',
            ])->default('scheduled');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};