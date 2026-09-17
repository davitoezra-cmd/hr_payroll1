<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("employee_employments", function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId("employee_id")
                ->constrained("employees")
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->date("start_date");
            $table->date("end_date")->nullable();

            $table
                ->enum("current_status", [
                    "ONBOARDING",
                    "ACTIVE",
                    "SUSPENDED",
                    "NOTICE_PERIOD",
                    "RESIGNED",
                    "TERMINATED",
                ])
                ->default("ONBOARDING");

            $table->dateTime("onboarding_started_at")->nullable();
            $table->dateTime("onboarding_completed_at")->nullable();

            $table->timestamps();

            $table->index("current_status");
            $table->index(["start_date", "end_date"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("employee_employments");
    }
};
