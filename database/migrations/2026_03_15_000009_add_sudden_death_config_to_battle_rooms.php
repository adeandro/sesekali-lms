<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add sudden death configuration to battle_rooms table.
     */
    public function up(): void
    {
        Schema::table('battle_rooms', function (Blueprint $table) {
            $table->integer('sudden_death_warning_seconds')->default(30)->after('penalty_hp');
            $table->integer('sudden_death_trigger_seconds')->nullable()->after('sudden_death_warning_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battle_rooms', function (Blueprint $table) {
            $table->dropColumn(['sudden_death_warning_seconds', 'sudden_death_trigger_seconds']);
        });
    }
};
