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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->string('topic');
            $table->enum('difficulty_level', ['easy', 'medium', 'hard']);
            $table->enum('question_type', ['multiple_choice', 'essay']);
            $table->longText('question_text');
            $table->string('option_a')->nullable();
            $table->string('option_b')->nullable();
            $table->string('option_c')->nullable();
            $table->string('option_d')->nullable();
            $table->string('option_e')->nullable();
            $table->string('correct_answer')->nullable();
            $table->longText('explanation')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes for performance
            $table->index('subject_id');
            $table->index('difficulty_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
