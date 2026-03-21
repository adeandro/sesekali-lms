<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')
                ->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')
                ->constrained('users')->cascadeOnDelete();
            $table->enum('grade_type', [
                'harian', 'uts', 'pts', 'uas', 'pas', 'praktik'
            ]);
            $table->decimal('score', 5, 2);
            $table->tinyInteger('semester')->unsigned();
            $table->string('academic_year', 9);
            $table->text('note')->nullable();
            $table->timestamps();

            // Satu siswa hanya boleh punya 1 nilai UTS/UAS per
            // mapel per semester per tahun (harian boleh banyak)
            // Catatan: unique constraint ini dikecualikan untuk
            // grade_type = 'harian' via validasi di controller
            $table->unique([
                'student_id', 'subject_id', 'grade_type',
                'semester', 'academic_year'
            ], 'unique_grade_per_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_grades');
    }
};
