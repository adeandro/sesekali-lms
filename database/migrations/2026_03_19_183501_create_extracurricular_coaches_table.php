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
        Schema::create('extracurricular_coaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')
                  ->constrained('extracurriculars')->cascadeOnDelete();
            $table->foreignId('teacher_id')
                  ->constrained('users')->cascadeOnDelete();
            $table->string('academic_year', 9);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['extracurricular_id', 'teacher_id', 'academic_year'], 'extracurricular_coaches_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracurricular_coaches');
    }
};
