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
        });

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
