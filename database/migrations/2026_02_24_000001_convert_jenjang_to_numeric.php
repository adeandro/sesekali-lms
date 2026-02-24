<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert existing Roman numeral jenjang values to numeric
        DB::statement("UPDATE exams SET jenjang = '10' WHERE jenjang IN ('X', 'x')");
        DB::statement("UPDATE exams SET jenjang = '11' WHERE jenjang IN ('XI', 'xi', 'Xi')");
        DB::statement("UPDATE exams SET jenjang = '12' WHERE jenjang IN ('XII', 'xii', 'Xii')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to Roman numerals
        DB::statement("UPDATE exams SET jenjang = 'X' WHERE jenjang = '10'");
        DB::statement("UPDATE exams SET jenjang = 'XI' WHERE jenjang = '11'");
        DB::statement("UPDATE exams SET jenjang = 'XII' WHERE jenjang = '12'");
    }
};
