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
        Schema::create('class_grade_averages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')
                  ->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')
                  ->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')
                  ->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('semester')->unsigned();
            $table->string('academic_year', 9);
            $table->tinyInteger('jenjang')->unsigned();
            $table->decimal('class_average', 5, 2);
            $table->timestamps();

            $table->unique(
                ['class_id', 'subject_id', 'semester', 'academic_year'],
                'unique_avg_per_period'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_grade_averages');
    }
};
