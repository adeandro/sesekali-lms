<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('seasonal_exp')->default(0)->after('total_exp');
            $table->unsignedInteger('career_exp')->default(0)->after('seasonal_exp');
            $table->tinyInteger('grade_level')->nullable()->after('grade')
                  ->comment('Numeric grade: 10, 11, or 12 for students');
            $table->string('active_theme_id')->nullable()->after('ui_theme')
                  ->comment('Battle Arena rank theme slug, e.g. legendary-golden');
            $table->string('alumni_year')->nullable()->after('active_theme_id')
                  ->comment('Set when grade 12 graduates, e.g. 2025/2026');
        });

        // Populate grade_level from existing `grade` column for students
        // grade is stored as string e.g. "10", "11", "12"
        \DB::statement("UPDATE users SET grade_level = CAST(grade AS UNSIGNED) WHERE role = 'student' AND grade REGEXP '^[0-9]+$'");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['seasonal_exp', 'career_exp', 'grade_level', 'active_theme_id', 'alumni_year']);
        });
    }
};
