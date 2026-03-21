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
        Schema::create('extracurricular_coach_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')
                  ->constrained('extracurricular_sessions')->cascadeOnDelete();
            $table->foreignId('coach_id')
                  ->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'tidak_hadir'])->default('hadir');
            $table->foreignId('recorded_by')
                  ->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['session_id', 'coach_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracurricular_coach_attendances');
    }
};
