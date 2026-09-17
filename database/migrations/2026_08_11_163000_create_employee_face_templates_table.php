<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_face_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                ->unique()
                ->constrained('employees')
                ->cascadeOnDelete();

            // Stored as encrypted JSON by the Eloquent encrypted:array cast.
            $table->longText('embedding');
            $table->string('engine', 50)->default('insightface');
            $table->string('engine_version', 50)->nullable();
            $table->string('model_name', 100);
            $table->unsignedSmallInteger('embedding_dimension');
            $table->boolean('is_active')->default(true);
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_face_templates');
    }
};
