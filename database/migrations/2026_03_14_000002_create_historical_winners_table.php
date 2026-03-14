<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historical_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
            $table->foreignId('battle_room_id')->nullable()->constrained('battle_rooms')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('rank');                          // 1, 2, 3 or null for participant
            $table->string('battle_room_name');                   // snapshot of name
            $table->string('battle_mode')->nullable();            // snapshot of mode
            $table->unsignedInteger('career_exp_snapshot')->default(0);
            $table->unsignedInteger('seasonal_exp_snapshot')->default(0);
            $table->string('theme_awarded')->nullable();          // e.g. legendary-golden
            $table->timestamp('archived_at')->useCurrent();
            $table->timestamps();

            $table->index(['season_id', 'rank']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_winners');
    }
};
