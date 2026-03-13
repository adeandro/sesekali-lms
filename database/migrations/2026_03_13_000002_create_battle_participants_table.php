<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battle_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('battle_room_id');
            $table->unsignedBigInteger('user_id');
            $table->string('class_id')->nullable();  // e.g. "X-IPA-1", derived from user grade+class_group
            $table->string('group_label')->nullable(); // for random-group mode
            $table->integer('hp')->default(100);
            $table->integer('correct_count')->default(0);
            $table->integer('wrong_count')->default(0);
            $table->integer('current_question_index')->default(0);
            $table->enum('status', ['active', 'disqualified', 'finished'])->default('active');
            $table->integer('rank')->nullable(); // final rank, set on finish
            $table->timestamp('disqualified_at')->nullable();
            $table->timestamp('finished_at')->nullable();  // when they answered all Qs
            $table->timestamp('last_seen_at')->nullable(); // heartbeat
            $table->timestamps();

            $table->foreign('battle_room_id')->references('id')->on('battle_rooms')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['battle_room_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battle_participants');
    }
};
