<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('face_identities', function (Blueprint $table) {
            $table->id();
            // One encrypted embedding represents one physical person.
            $table->longText('embedding');
            $table->string('engine', 50)->default('insightface');
            $table->string('engine_version', 50)->nullable();
            $table->string('model_name', 100);
            $table->unsignedSmallInteger('embedding_dimension');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('face_identity_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('face_identity_id')
                ->constrained('face_identities')
                ->cascadeOnDelete();
            $table->string('account_type', 32);
            $table->unsignedBigInteger('account_id');
            $table->timestamps();

            // A single account may belong to only one biometric identity.
            $table->unique(['account_type', 'account_id'], 'face_identity_account_unique');
            $table->unique(
                ['face_identity_id', 'account_type', 'account_id'],
                'face_identity_mapping_unique'
            );
            $table->index(['face_identity_id', 'account_type'], 'face_identity_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_identity_accounts');
        Schema::dropIfExists('face_identities');
    }
};
