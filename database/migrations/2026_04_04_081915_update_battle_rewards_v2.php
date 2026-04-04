<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('battle_rooms', function (Blueprint $table) {
            // Rename old reward_physical if it exists, or just drop and recreate
            if (Schema::hasColumn('battle_rooms', 'reward_physical')) {
                $table->dropColumn('reward_physical');
            }
            $table->string('reward_rank1_physical')->nullable()->after('reward_participant_theme_id');
            $table->string('reward_rank2_physical')->nullable()->after('reward_rank1_physical');
            $table->string('reward_rank3_physical')->nullable()->after('reward_rank2_physical');
        });

        Schema::table('battle_participants', function (Blueprint $table) {
            $table->string('physical_reward')->nullable()->after('rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battle_rooms', function (Blueprint $table) {
            $table->dropColumn(['reward_rank1_physical', 'reward_rank2_physical', 'reward_rank3_physical']);
            $table->string('reward_physical')->nullable();
        });

        Schema::table('battle_participants', function (Blueprint $table) {
            $table->dropColumn('physical_reward');
        });
    }
};
