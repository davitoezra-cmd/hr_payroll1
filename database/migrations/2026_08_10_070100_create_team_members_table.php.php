<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Tim
            |--------------------------------------------------------------------------
            */
            $table->foreignId('team_id')
                ->constrained('teams')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Anggota tim
            |--------------------------------------------------------------------------
            |
            */
            $table->unsignedBigInteger('member_id');
            $table->string('member_type');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */
            $table->index([
                'member_type',
                'member_id',
            ]);

            $table->index('team_id');

            /*
            |--------------------------------------------------------------------------
            | Mencegah orang yang sama masuk dua kali
            | ke tim yang sama
            |--------------------------------------------------------------------------
            */
            $table->unique([
                'team_id',
                'member_id',
                'member_type',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};