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
        Schema::create('business_trips', function (Blueprint $table) {

            $table->id();

            // Employee yang mengajukan
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            // Tanggal dinas luar
            $table->date('trip_date');

            // Tujuan dinas
            $table->string('destination');

            // Keperluan dinas
            $table->text('purpose');

            // Status approval
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'completed'
            ])->default('pending');

            // Admin yang approve
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Waktu approve
            $table->timestamp('approved_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | CHECK IN
            |--------------------------------------------------------------------------
            */

            $table->time('check_in')->nullable();

            $table->string('check_in_photo')->nullable();

            $table->decimal('check_in_latitude', 10, 7)->nullable();

            $table->decimal('check_in_longitude', 10, 7)->nullable();

            /*
            |--------------------------------------------------------------------------
            | CHECK OUT
            |--------------------------------------------------------------------------
            */

            $table->time('check_out')->nullable();

            $table->string('check_out_photo')->nullable();

            $table->decimal('check_out_latitude', 10, 7)->nullable();

            $table->decimal('check_out_longitude', 10, 7)->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_trips');
    }
};
