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
        DB::table('settings')->insertOrIgnore([
            ['key' => 'enable_gamification', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'enable_leaderboard', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['enable_gamification', 'enable_leaderboard'])->delete();
    }
};
