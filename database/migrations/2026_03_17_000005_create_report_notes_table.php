<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')
                ->constrained('classes')->cascadeOnDelete();
            $table->foreignId('teacher_id')
                ->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('semester')->unsigned();
            $table->string('academic_year', 9);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique([
                'student_id', 'semester', 'academic_year'
            ], 'unique_note_per_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_notes');
    }
};
