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
        Schema::create("employee_separations", function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId("employment_id")
                ->constrained("employee_employments")
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->enum("separation_type", ["RESIGNATION", "TERMINATION"]);

            $table->string("reason", 500);

            $table->date("notice_date")->nullable();
            $table->date("last_working_date")->nullable();
            $table->date("effective_date")->nullable();

            $table
                ->enum("process_status", [
                    "SUBMITTED",
                    "APPROVED",
                    "REJECTED",
                    "CANCELLED",
                    "COMPLETED",
                ])
                ->default("SUBMITTED");

            $table->text("notes")->nullable();

            $table->dateTime("submitted_at")->useCurrent();

            $table
                ->foreignId("approved_by")
                ->nullable()
                ->constrained("users")
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->dateTime("approved_at")->nullable();

            $table
                ->foreignId("processed_by")
                ->nullable()
                ->constrained("users")
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->dateTime("processed_at")->nullable();

            $table->dateTime("offboarding_started_at")->nullable();
            $table->dateTime("offboarding_completed_at")->nullable();

            $table->timestamps();

            $table->index("separation_type");
            $table->index("process_status");
            $table->index("last_working_date");
            $table->index("effective_date");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("employee_separations");
    }
};
