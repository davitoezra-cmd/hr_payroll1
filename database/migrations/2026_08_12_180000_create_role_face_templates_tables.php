<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_face_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $this->faceColumns($table);
        });

        Schema::create('supervisor_face_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->unique()->constrained('supervisors')->cascadeOnDelete();
            $this->faceColumns($table);
        });

        Schema::create('finance_face_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_id')->unique()->constrained('finances')->cascadeOnDelete();
            $this->faceColumns($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_face_templates');
        Schema::dropIfExists('supervisor_face_templates');
        Schema::dropIfExists('user_face_templates');
    }

    private function faceColumns(Blueprint $table): void
    {
        $table->longText('embedding');
        $table->string('engine', 50)->default('insightface');
        $table->string('engine_version', 50)->nullable();
        $table->string('model_name', 100);
        $table->unsignedSmallInteger('embedding_dimension');
        $table->boolean('is_active')->default(true);
        $table->timestamp('enrolled_at')->nullable();
        $table->timestamp('last_verified_at')->nullable();
        $table->timestamps();
    }
};
