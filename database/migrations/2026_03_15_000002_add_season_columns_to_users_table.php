<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add season-related columns to users table for gamification tracking.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('prestige_count')->default(0)->after('current_level');
            $table->bigInteger('exp_total_alltime')->default(0)->after('total_exp');
            $table->unsignedBigInteger('current_season_id')->nullable()->after('exp_total_alltime');
            $table->integer('rank_global')->nullable()->after('current_season_id');
            $table->integer('rank_delta')->default(0)->after('rank_global');

            $table->foreign('current_season_id')->references('id')->on('seasons')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_season_id']);
            $table->dropColumn([
                'prestige_count',
                'exp_total_alltime',
                'current_season_id',
                'rank_global',
                'rank_delta',
            ]);
        });
    }
};
