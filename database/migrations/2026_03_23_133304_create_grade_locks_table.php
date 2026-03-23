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
        Schema::create('grade_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')
                  ->constrained()->cascadeOnDelete();
            $table->foreignId('locked_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->tinyInteger('semester');
            $table->string('academic_year', 20);
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['subject_id', 'semester', 'academic_year'],
                'grade_locks_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_locks');
    }
};
