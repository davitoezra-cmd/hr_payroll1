<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_payment_proofs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('finance_id')
                ->nullable()
                ->constrained('finances')
                ->nullOnDelete();

            $table->foreignId('employee_id')
      ->constrained('employees')
      ->cascadeOnDelete();

            $table->enum('bpjs_type', [
                'kesehatan',
                'ketenagakerjaan'
            ]);

            $table->string('period');

            $table->string('document_name');

            $table->string('file_path');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_payment_proofs');
    }
};