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
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                  ->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')
                  ->constrained('classes')->cascadeOnDelete();
            $table->tinyInteger('semester')->unsigned();
            $table->string('academic_year', 9);
            $table->unsignedInteger('sick_days')->default(0);   // sakit
            $table->unsignedInteger('permit_days')->default(0); // izin
            $table->unsignedInteger('alpha_days')->default(0);  // tanpa keterangan
            $table->timestamps();

            $table->unique(
                ['student_id', 'semester', 'academic_year'],
                'unique_attendance_per_period'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
