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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('primary_color');
            $table->string('secondary_color');
            $table->string('glow_color');
            $table->string('bg_color');
            $table->string('text_color');
            $table->string('dark_color')->nullable();
            $table->string('surface_color')->nullable();
            $table->boolean('is_unlocked_by_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('min_level')->default(0); // Additional: unlock by level
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
