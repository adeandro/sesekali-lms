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
        // Drop the old basic table if it exists
        Schema::dropIfExists('student_achievements');

        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description');
            $table->string('icon');
            $table->string('color');
            $table->timestamps();
        });

        Schema::create('achievement_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained()->onDelete('cascade');
            $table->timestamp('achieved_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'achievement_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_user');
        Schema::dropIfExists('achievements');
    }
};
