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
        Schema::create('extracurricular_session_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')
                  ->constrained('extracurricular_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')
                  ->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alfa'])->default('hadir');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['session_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracurricular_session_attendances');
    }
};
