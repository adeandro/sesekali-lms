<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battle_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 8)->unique(); // short join code
            $table->enum('mode', ['individual', 'group', 'class'])->default('individual');
            $table->enum('source_type', ['exam', 'question_bank'])->default('exam');
            $table->unsignedBigInteger('source_id')->nullable(); // exam_id or null
            $table->unsignedBigInteger('created_by');
            $table->integer('winner_count')->default(3);
            $table->integer('duration_minutes')->default(30);
            $table->integer('penalty_hp')->default(20);
            $table->boolean('lock_on_start')->default(true);
            $table->enum('status', ['waiting', 'countdown', 'ongoing', 'finished'])->default('waiting');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('total_questions')->default(0);
            $table->json('question_ids')->nullable(); // cached list of question IDs
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battle_rooms');
    }
};
