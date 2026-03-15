<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add battle-specific performance and power-up columns to battle_participants table.
     */
    public function up(): void
    {
        Schema::table('battle_participants', function (Blueprint $table) {
            $table->integer('current_streak')->default(0)->after('rank');
            $table->integer('max_streak')->default(0)->after('current_streak');
            $table->decimal('exp_multiplier', 3, 1)->default(1.0)->after('max_streak');
            $table->boolean('comeback_active')->default(false)->after('exp_multiplier');
            $table->integer('comeback_questions_left')->default(0)->after('comeback_active');
            $table->enum('active_powerup', ['none', 'shield', 'boost', 'freeze'])->default('none')->after('comeback_questions_left');
            $table->integer('powerup_used_count')->default(0)->after('active_powerup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battle_participants', function (Blueprint $table) {
            $table->dropColumn([
                'current_streak',
                'max_streak',
                'exp_multiplier',
                'comeback_active',
                'comeback_questions_left',
                'active_powerup',
                'powerup_used_count',
            ]);
        });
    }
};
