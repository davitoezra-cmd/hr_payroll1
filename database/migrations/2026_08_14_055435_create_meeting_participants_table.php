<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_participants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('meeting_id')
                ->constrained('meetings')
                ->cascadeOnDelete();

            // Bisa Employee, Supervisor, atau Finance
            $table->morphs('participant');

            $table->timestamps();

            $table->unique(
                ['meeting_id', 'participant_type', 'participant_id'],
                'meeting_participant_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_participants');
    }
};