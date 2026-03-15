<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create powerup_cards table to manage consumable items in battle arena.
     */
    public function up(): void
    {
        Schema::create('powerup_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('battle_room_id')->nullable();
            $table->unsignedBigInteger('season_id')->nullable();
            $table->enum('type', ['shield', 'boost', 'freeze']);
            $table->enum('status', ['available', 'used', 'expired'])->default('available');
            $table->timestamp('acquired_at')->useCurrent();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('battle_room_id')->references('id')->on('battle_rooms')->onDelete('set null');
            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('powerup_cards');
    }
};
