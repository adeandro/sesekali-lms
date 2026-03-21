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
        Schema::create('student_personalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                  ->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')
                  ->constrained('classes')->cascadeOnDelete();
            $table->foreignId('teacher_id')
                  ->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('semester')->unsigned();
            $table->string('academic_year', 9);
            $table->enum('discipline', [
                'Sangat Baik', 'Baik', 'Cukup', 'Kurang'
            ])->nullable(); // kedisiplinan
            $table->enum('behavior', [
                'Sangat Baik', 'Baik', 'Cukup', 'Kurang'
            ])->nullable(); // kelakuan
            $table->enum('neatness', [
                'Sangat Baik', 'Baik', 'Cukup', 'Kurang'
            ])->nullable(); // kerapian
            $table->timestamps();

            $table->unique(
                ['student_id', 'semester', 'academic_year'],
                'unique_personality_per_period'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_personalities');
    }
};
