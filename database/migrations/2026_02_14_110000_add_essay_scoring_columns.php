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
        // Add essay_score to exam_answers if column doesn't exist
        if (Schema::hasTable('exam_answers') && !Schema::hasColumn('exam_answers', 'essay_score')) {
            Schema::table('exam_answers', function (Blueprint $table) {
                $table->decimal('essay_score', 5, 2)->nullable()->after('is_correct')->comment('Teacher essay score (0-10)');
            });
        }

        // Add score_essay to exam_attempts if column doesn't exist
        if (Schema::hasTable('exam_attempts') && !Schema::hasColumn('exam_attempts', 'score_essay')) {
            Schema::table('exam_attempts', function (Blueprint $table) {
                $table->decimal('score_essay', 5, 2)->nullable()->after('score_mc')->comment('Calculated essay score (0-100)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('exam_answers') && Schema::hasColumn('exam_answers', 'essay_score')) {
            Schema::table('exam_answers', function (Blueprint $table) {
                $table->dropColumn('essay_score');
            });
        }

        if (Schema::hasTable('exam_attempts') && Schema::hasColumn('exam_attempts', 'score_essay')) {
            Schema::table('exam_attempts', function (Blueprint $table) {
                $table->dropColumn('score_essay');
            });
        }
    }
};
