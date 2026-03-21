<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_grades', function (Blueprint $table) {
            // Sprint 2: jenjang untuk scoping nilai per tingkat kelas
            $table->tinyInteger('jenjang')->unsigned()->nullable()->after('academic_year');
        });
    }

    public function down(): void
    {
        Schema::table('manual_grades', function (Blueprint $table) {
            $table->dropColumn('jenjang');
        });
    }
};
