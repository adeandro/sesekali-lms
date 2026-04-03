<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop tabel lama beserta foreign keys
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('battle_answers');
        Schema::dropIfExists('battle_participants');
        Schema::dropIfExists('battle_rooms');
        Schema::enableForeignKeyConstraints();

        // ── battle_rooms ─────────────────────────
        Schema::create('battle_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('token', 8)->unique();
            $table->string('name');
            $table->enum('mode', [
                'individual', 'group', 'class'
            ])->default('individual');

            // Sumber soal
            $table->enum('source_type', ['exam', 'manual'])
                  ->default('exam');
            $table->unsignedBigInteger('source_id')
                  ->nullable(); // exam_id jika dari exam
            $table->json('question_ids');
            $table->unsignedInteger('total_questions')
                  ->default(10);
            $table->unsignedInteger('duration_per_question')
                  ->default(20); // detik

            // Konfigurasi grup
            $table->unsignedInteger('group_count')
                  ->nullable(); // 2-8, hanya mode group
            $table->json('group_names')->nullable();
            // contoh: ["Merah","Biru","Hijau"]
            $table->unsignedInteger('max_per_group')
                  ->nullable();

            // Status & flow
            $table->enum('status', [
                'waiting', 'ongoing', 'finished'
            ])->default('waiting');
            $table->unsignedInteger('current_q_index')
                  ->default(0);

            // Reward
            $table->unsignedInteger('reward_rank1_exp')
                  ->default(500);
            $table->unsignedInteger('reward_rank2_exp')
                  ->default(300);
            $table->unsignedInteger('reward_rank3_exp')
                  ->default(150);
            $table->unsignedBigInteger('reward_theme_id')
                  ->nullable();
            $table->text('reward_physical')->nullable();
            // contoh: "Voucher Kantin Rp 10.000"

            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── battle_participants ───────────────────
        Schema::create('battle_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('battle_room_id');
            $table->foreign('battle_room_id')
                  ->references('id')->on('battle_rooms')
                  ->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->string('group_label')->nullable();
            // nama grup: "Merah", "Biru", dll

            // Final scores (diisi saat FINISH)
            $table->unsignedInteger('total_score')
                  ->default(0);
            $table->unsignedInteger('correct_count')
                  ->default(0);
            $table->unsignedInteger('wrong_count')
                  ->default(0);
            $table->unsignedInteger('rank')->nullable();
            // rank individual

            $table->timestamp('joined_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            // Satu siswa hanya bisa join 1x per room
            $table->unique(['battle_room_id', 'user_id']);
        });

        // ── battle_answers ────────────────────────
        Schema::create('battle_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('battle_room_id');
            $table->foreign('battle_room_id')
                  ->references('id')->on('battle_rooms')
                  ->onDelete('cascade');
            $table->unsignedBigInteger('battle_participant_id');
            $table->foreign('battle_participant_id')
                  ->references('id')->on('battle_participants')
                  ->onDelete('cascade');
            $table->unsignedBigInteger('question_id');
            $table->unsignedInteger('q_index');
            $table->enum('chosen_option', [
                'a', 'b', 'c', 'd', 'e'
            ])->nullable(); // null = tidak menjawab
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('score_earned')
                  ->default(0);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            // Idempotency — 1 jawaban per soal per peserta
            $table->unique([
                'battle_participant_id', 'q_index'
            ]);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('battle_answers');
        Schema::dropIfExists('battle_participants');
        Schema::dropIfExists('battle_rooms');
        Schema::enableForeignKeyConstraints();
    }
};
