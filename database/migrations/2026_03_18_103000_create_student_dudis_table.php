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
        Schema::create('student_dudis', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $blueprint->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $blueprint->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $blueprint->tinyInteger('semester')->unsigned();
            $blueprint->string('academic_year', 20);
            $blueprint->string('activity_name', 191);
            $blueprint->string('institution_name', 191);
            $blueprint->string('institution_address', 191)->nullable();
            $blueprint->string('period', 100);
            $blueprint->string('grade', 20)->nullable();
            $blueprint->tinyInteger('sort_order')->unsigned()->default(0);
            $blueprint->timestamps();

            $blueprint->index(['student_id', 'semester', 'academic_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_dudis');
    }
};
