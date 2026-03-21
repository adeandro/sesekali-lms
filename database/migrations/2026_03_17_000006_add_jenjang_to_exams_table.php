<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // exams.jenjang may already exist in older schema versions — guard against duplicate
            if (!Schema::hasColumn('exams', 'jenjang')) {
                $table->tinyInteger('jenjang')->unsigned()->nullable()->after('academic_year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (Schema::hasColumn('exams', 'jenjang')) {
                $table->dropColumn('jenjang');
            }
        });
    }
};
