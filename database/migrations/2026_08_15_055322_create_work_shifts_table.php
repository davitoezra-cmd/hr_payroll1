<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('work_shifts', function (Blueprint $table) {
            $table->id();
           

            $table->string('code', 30)->unique();
            $table->string('name', 100);

            $table->time('jam_masuk');
            $table->time('batas_telat');

            $table->time('mulai_bonus_datang')->nullable();

            $table->time('jam_pulang');

            $table->time('mulai_lembur')->nullable();

            $table->boolean('lintas_hari')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_shifts');
    }
};
