<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create hall_of_fame table to preserve final season rankings and snapshots.
     */
    public function up(): void
    {
        Schema::create('hall_of_fame', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('season_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('rank');
            $table->decimal('app_points_final', 10, 2);
            $table->integer('level_final');
            $table->json('achievements_snapshot')->nullable();
            $table->json('season_history_snapshot')->nullable();
            $table->string('display_name');
            $table->string('avatar_key')->nullable();
            $table->string('class_name')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('cascade');
            // Data HoF must remain even if account is deleted
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hall_of_fame');
    }
};
