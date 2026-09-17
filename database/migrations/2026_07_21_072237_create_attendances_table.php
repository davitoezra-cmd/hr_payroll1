<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('attendances', function (Blueprint $table) {

    $table->id();

    $table->foreignId('employee_id')
        ->constrained('employees')
        ->cascadeOnDelete();

    $table->date('attendance_date');

    $table->string('image_selfie')->nullable();


    // waktu
    $table->time('check_in')->nullable();
    $table->time('check_out')->nullable();


    // status
    $table->enum('status', [
        'hadir',
        'terlambat',
        
        
    ])->default('hadir');

    $table->enum('metode',[
            'selfie',
            'qr'
        ])
        ->default('selfie');


    // GPS Check In
    $table->decimal('latitude',10,7)
        ->nullable();

    $table->decimal('longitude',10,7)
        ->nullable();


    // GPS Check Out
    $table->decimal('checkout_latitude',10,7)
        ->nullable();

    $table->decimal('checkout_longitude',10,7)
        ->nullable();



    
    $table->time('check_in_limit')
        ->nullable();



    


    $table->boolean('bonus_didapat')
        ->default(false);

   

    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};