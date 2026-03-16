<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add status enum after is_active
            $table->enum('status', ['Aktif', 'Alumni', 'Nonaktif'])
                  ->default('Aktif')
                  ->after('is_active')
                  ->comment('Aktif=masih belajar, Alumni=lulus grade 12, Nonaktif=DO/pindah');

            $table->tinyInteger('grade_level')->nullable()->after('grade')
                  ->comment('Numeric grade: 10, 11, or 12 for students');
            $table->string('active_theme_id')->nullable()->after('ui_theme')
                  ->comment('Battle Arena rank theme slug');
            $table->string('alumni_year')->nullable()->after('active_theme_id')
                  ->comment('Set when grade 12 graduates');
        });

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
