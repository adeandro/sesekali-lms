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
        Schema::create('extracurricular_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')
                  ->constrained('extracurriculars')->cascadeOnDelete();
            $table->string('academic_year', 9);
            $table->tinyInteger('semester')->unsigned(); // 1 atau 2
            $table->date('date'); // tanggal pertemuan
            $table->string('topic'); // materi/topik
            $table->text('notes')->nullable(); // catatan jurnal
            $table->foreignId('created_by')
                  ->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['extracurricular_id', 'academic_year', 'semester'], 'extracurricular_sessions_main_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracurricular_sessions');
    }
};
