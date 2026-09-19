<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('typing_tests', 'max_attempts')) {
            Schema::table('typing_tests', function (Blueprint $table) {
                $table->unsignedTinyInteger('max_attempts')->default(2)->after('word_count');
            });
        }

        Schema::table('typing_attempts', function (Blueprint $table) {
            $table->dropForeign(['typing_test_id']);
            $table->dropUnique('typing_attempts_typing_test_id_student_id_unique');
            if (!Schema::hasColumn('typing_attempts', 'attempt_number')) {
                $table->unsignedTinyInteger('attempt_number')->default(1)->after('student_id');
            }
            $table->unique(['typing_test_id', 'student_id', 'attempt_number'], 'typing_attempts_test_student_attempt_unique');
            $table->foreign('typing_test_id')->references('id')->on('typing_tests')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('typing_attempts', function (Blueprint $table) {
            $table->dropForeign(['typing_test_id']);
            $table->dropUnique('typing_attempts_test_student_attempt_unique');
            if (Schema::hasColumn('typing_attempts', 'attempt_number')) {
                $table->dropColumn('attempt_number');
            }
            $table->unique(['typing_test_id', 'student_id'], 'typing_attempts_typing_test_id_student_id_unique');
            $table->foreign('typing_test_id')->references('id')->on('typing_tests')->onDelete('cascade');
        });

        if (Schema::hasColumn('typing_tests', 'max_attempts')) {
            Schema::table('typing_tests', function (Blueprint $table) {
                $table->dropColumn('max_attempts');
            });
        }
    }
};


