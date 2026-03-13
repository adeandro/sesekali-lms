<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battle_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('battle_participant_id');
            $table->unsignedBigInteger('question_id');
            $table->string('chosen_option')->nullable(); // a, b, c, d, e
            $table->boolean('is_correct')->default(false);
            $table->integer('hp_delta')->default(0); // negative = penalty
            $table->timestamp('answered_at')->useCurrent();

            $table->foreign('battle_participant_id')->references('id')->on('battle_participants')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battle_answers');
    }
};
