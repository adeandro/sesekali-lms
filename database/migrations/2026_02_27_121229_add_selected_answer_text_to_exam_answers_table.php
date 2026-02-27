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
        Schema::table('exam_answers', function (Blueprint $table) {
            // Store the actual text that was displayed to student
            // Critical for handling shuffled options - preserves what they actually saw
            $table->text('selected_answer_text')->nullable()->after('selected_answer');
            $table->text('correct_answer_text')->nullable()->after('selected_answer_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->dropColumn(['selected_answer_text', 'correct_answer_text']);
        });
    }
};
