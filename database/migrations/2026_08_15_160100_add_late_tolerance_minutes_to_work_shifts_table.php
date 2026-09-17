<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_shifts', function (Blueprint $table) {
            $table->unsignedSmallInteger('late_tolerance_minutes')
                ->default(10)
                ->after('jam_masuk');
        });

        DB::table('work_shifts')->orderBy('id')->get()->each(function ($shift) {
            if (!$shift->jam_masuk || !$shift->batas_telat) {
                return;
            }

            $start = new DateTime($shift->jam_masuk);
            $limit = new DateTime($shift->batas_telat);
            $minutes = (int) round(($limit->getTimestamp() - $start->getTimestamp()) / 60);

            if ($minutes < 0) {
                $minutes += 24 * 60;
            }

            DB::table('work_shifts')
                ->where('id', $shift->id)
                ->update(['late_tolerance_minutes' => min($minutes, 180)]);
        });
    }

    public function down(): void
    {
        Schema::table('work_shifts', function (Blueprint $table) {
            $table->dropColumn('late_tolerance_minutes');
        });
    }
};
