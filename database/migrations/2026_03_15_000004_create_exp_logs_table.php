<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create exp_logs table to track EXP gains across different sources and seasons.
     */
    public function up(): void
    {
        Schema::create('exp_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('season_id')->nullable();
            $table->enum('source', ['exam', 'battle', 'streak', 'comeback', 'prestige']);
            $table->integer('exp_amount');
            $table->decimal('multiplier', 3, 1)->default(1.0);
            $table->unsignedBigInteger('reference_id')->nullable(); // e.g., attempt_id, battle_room_id
            $table->string('reference_type')->nullable();
            $table->timestamp('earned_at')->useCurrent();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exp_logs');
    }
};
