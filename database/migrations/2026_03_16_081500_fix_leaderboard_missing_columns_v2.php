<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to fix missing columns for leaderboard.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'seasonal_exp')) {
                $table->unsignedInteger('seasonal_exp')->default(0)->after('total_exp');
            }

            if (!Schema::hasColumn('users', 'career_exp')) {
                $table->unsignedInteger('career_exp')->default(0)->after('seasonal_exp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['seasonal_exp', 'career_exp']);
        });
    }
};
