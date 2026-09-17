<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_locations', function (Blueprint $table) {
            $table->id();

            // Nama lokasi absensi
            $table->string('name');

            // Titik lokasi kantor / area absensi
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // Batas jarak absensi dalam meter
            $table->unsignedInteger('radius_meter')->default(100);

            // Apakah lokasi ini sedang digunakan
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_locations');
    }
};

