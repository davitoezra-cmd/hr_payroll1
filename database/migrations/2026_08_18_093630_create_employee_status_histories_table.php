<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("employee_status_histories", function (
            Blueprint $table,
        )
         {
            $table->id();

            $table
                ->foreignId("employment_id")
                ->constrained("employee_employments")
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->enum("status", [
                "ONBOARDING",
                "ACTIVE",
                "SUSPENDED",
                "NOTICE_PERIOD",
                "RESIGNED",
                "TERMINATED",
            ]);

            $table->dateTime("effective_from");
            $table->dateTime("effective_to")->nullable();

            $table->string("reason", 500)->nullable();

            $table
                ->foreignId("changed_by")
                ->nullable()
                ->constrained("users")
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();

            $table->index("status", "employee_status_status_index");

            $table->index(
                ["employment_id", "effective_from", "effective_to"],
                "employee_status_period_index",
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("employee_status_histories");
    }
};
