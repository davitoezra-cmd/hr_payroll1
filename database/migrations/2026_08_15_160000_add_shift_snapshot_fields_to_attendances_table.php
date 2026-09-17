<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('employee_shift_schedule_id')
                ->nullable()
                ->after('employee_id')
                ->constrained('employee_shift_schedules')
                ->nullOnDelete();

            $table->time('scheduled_check_in')->nullable()->after('check_out');
            $table->time('scheduled_check_out')->nullable()->after('scheduled_check_in');
            $table->unsignedSmallInteger('late_tolerance_minutes')->nullable()->after('scheduled_check_out');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_shift_schedule_id');
            $table->dropColumn([
                'scheduled_check_in',
                'scheduled_check_out',
                'late_tolerance_minutes',
            ]);
        });
    }
};
