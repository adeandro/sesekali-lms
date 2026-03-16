<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('status', ['Aktif', 'Alumni', 'Nonaktif'])
                      ->default('Aktif')
                      ->after('is_active')
                      ->comment('Aktif=masih belajar, Alumni=lulus grade 12, Nonaktif=DO/pindah');
            });
        }

        if (!Schema::hasColumn('users', 'grade_level')) {
            Schema::table('users', function (Blueprint $table) {
                $table->tinyInteger('grade_level')->nullable()->after('grade')
                      ->comment('Numeric grade: 10, 11, or 12 for students');
            });
        }

        if (!Schema::hasColumn('users', 'active_theme_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('active_theme_id')->nullable()->after('ui_theme')
                      ->comment('Battle Arena rank theme slug');
            });
        }

        if (!Schema::hasColumn('users', 'alumni_year')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('alumni_year')->nullable()->after('active_theme_id')
                      ->comment('Set when grade 12 graduates');
            });
        }

        if (!Schema::hasColumn('users', 'seasonal_exp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('seasonal_exp')->default(0)->after('total_exp');
            });
        }

        if (!Schema::hasColumn('users', 'career_exp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('career_exp')->default(0)->after('seasonal_exp');
            });
        }

        // Populate grade_level from existing `grade` column for students
        // grade is stored as string e.g. "10", "11", "12"
        DB::statement("UPDATE users SET grade_level = CAST(grade AS UNSIGNED) WHERE role = 'student' AND grade REGEXP '^[0-9]+$'");

        // Populate from is_active: Aktif if is_active=1, else Nonaktif
        // Students where alumni_year IS set → Alumni
        DB::statement("
            UPDATE users
            SET status = CASE
                WHEN role = 'student' AND alumni_year IS NOT NULL THEN 'Alumni'
                WHEN role = 'student' AND is_active = 0               THEN 'Nonaktif'
                ELSE 'Aktif'
            END
            WHERE role = 'student'
        ");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
