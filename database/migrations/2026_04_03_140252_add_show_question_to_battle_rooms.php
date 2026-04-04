<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('battle_rooms', function (Blueprint $table) {
            $table->boolean('show_question_on_device')
                  ->default(false)
                  ->after('duration_per_question');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battle_rooms', function (Blueprint $table) {
            $table->dropColumn('show_question_on_device');
        });
    }
};
