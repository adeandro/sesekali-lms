<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->enum('exam_type', [
                'latihan', 'harian', 'uts', 'pts', 'uas', 'pas'
            ])->default('harian')->nullable()->after('status');

            $table->tinyInteger('semester')
                ->unsigned()->nullable()->after('exam_type');

            $table->string('academic_year', 9)
                ->nullable()->after('semester');

            $table->boolean('include_in_report')
                ->default(true)->after('academic_year');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['exam_type', 'semester', 'academic_year', 'include_in_report']);
        });
    }
};
