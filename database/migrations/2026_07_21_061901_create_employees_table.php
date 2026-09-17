<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('employee_code')->unique();

            $table->string('name');
            $table->string('email')->unique();
            $table->string('nama_bank')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('nama_rekening')->nullable();
            $table->string('phone')->nullable();

            $table->string('password');

          

            // Status
            $table->boolean('is_active')->default(true);
             $table->time('check_in_limit')
        ->nullable();



    

    $table->boolean('bonus_didapat')
        ->default(false);

           
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};