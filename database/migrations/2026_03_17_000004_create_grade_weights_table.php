<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')
                ->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')
                ->constrained('users')->cascadeOnDelete();
                $table->tinyInteger('jenjang')->unsigned()->nullable();
            $table->tinyInteger('semester')->unsigned();
            $table->string('academic_year', 9);
            $table->decimal('weight_harian', 5, 2)->default(40);
            $table->decimal('weight_uts',    5, 2)->default(30);
            $table->decimal('weight_uas',    5, 2)->default(30);
            // Total weight_harian + weight_uts + weight_uas HARUS = 100
            // Validasi di controller, bukan di DB level
            $table->timestamps();

            $table->unique([
                'subject_id', 'teacher_id','jenjang', 'semester', 'academic_year'
            ], 'unique_weight_per_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_weights');
    }
};
