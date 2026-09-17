<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_qrs', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->uuid('token')->unique();

             $table->string('image')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamp('expired_at')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_qrs');
    }
};