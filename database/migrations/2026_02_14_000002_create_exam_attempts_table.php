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
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted'])->default('in_progress');
            $table->decimal('score_mc', 5, 2)->nullable();
            $table->decimal('score_essay', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->timestamps();

            // Unique constraint
            $table->unique(['exam_id', 'student_id']);

            // Indexes
            $table->index('student_id');
            $table->index('status');
            $table->index(['exam_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
