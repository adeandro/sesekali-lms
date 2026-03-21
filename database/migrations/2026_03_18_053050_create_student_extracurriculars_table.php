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
        Schema::create('student_extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                  ->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')
                  ->constrained('classes')->cascadeOnDelete();
            $table->foreignId('teacher_id')
                  ->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('semester')->unsigned();
            $table->string('academic_year', 9);
            $table->string('name', 191); // nama ekskul
            $table->enum('grade', [
                'Sangat Baik', 'Baik', 'Cukup', 'Kurang'
            ]);
            $table->text('note')->nullable();
            $table->timestamps();
            // Tidak ada unique constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_extracurriculars');
    }
};
