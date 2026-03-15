<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create subject_leaderboards table to track academic performance per subject.
     */
    public function up(): void
    {
        Schema::create('subject_leaderboards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('season_id')->nullable();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('average_score', 5, 2);
            $table->integer('exam_count');
            $table->integer('rank');
            $table->timestamps();

            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('set null');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_leaderboards');
    }
};
