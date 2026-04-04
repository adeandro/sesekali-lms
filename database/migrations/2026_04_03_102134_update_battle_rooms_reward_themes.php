<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('battle_rooms', function (Blueprint $table) {
            // Hapus kolom lama (tidak ada foreign key constraint)
            $table->dropColumn('reward_theme_id');

            // Tambah 4 kolom baru per rank
            $table->unsignedBigInteger('reward_rank1_theme_id')
                  ->nullable()->after('reward_rank1_exp');
            $table->unsignedBigInteger('reward_rank2_theme_id')
                  ->nullable()->after('reward_rank2_exp');
            $table->unsignedBigInteger('reward_rank3_theme_id')
                  ->nullable()->after('reward_rank3_exp');
            $table->unsignedBigInteger('reward_participant_theme_id')
                  ->nullable()->after('reward_rank3_theme_id');

            // Foreign keys dengan nullOnDelete
            $table->foreign('reward_rank1_theme_id')
                  ->references('id')->on('themes')
                  ->nullOnDelete();
            $table->foreign('reward_rank2_theme_id')
                  ->references('id')->on('themes')
                  ->nullOnDelete();
            $table->foreign('reward_rank3_theme_id')
                  ->references('id')->on('themes')
                  ->nullOnDelete();
            $table->foreign('reward_participant_theme_id')
                  ->references('id')->on('themes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('battle_rooms', function (Blueprint $table) {
            $table->dropForeign(['reward_rank1_theme_id']);
            $table->dropForeign(['reward_rank2_theme_id']);
            $table->dropForeign(['reward_rank3_theme_id']);
            $table->dropForeign(['reward_participant_theme_id']);
            $table->dropColumn([
                'reward_rank1_theme_id',
                'reward_rank2_theme_id',
                'reward_rank3_theme_id',
                'reward_participant_theme_id',
            ]);

            // Restore kolom lama
            $table->unsignedBigInteger('reward_theme_id')
                  ->nullable()->after('reward_rank3_exp');
        });
    }
};
