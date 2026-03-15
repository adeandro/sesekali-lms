<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add consistency tracking columns to users table.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('consecutive_exam_weeks')->default(0)->after('rank_delta');
            $table->integer('last_exam_week')->nullable()->after('consecutive_exam_weeks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['consecutive_exam_weeks', 'last_exam_week']);
        });
    }
};
