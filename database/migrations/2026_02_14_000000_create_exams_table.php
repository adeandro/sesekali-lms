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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('jenjang')->nullable()->comment('Grade level: 10, 11, 12');
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->integer('duration_minutes');
            $table->integer('total_questions');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->boolean('randomize_questions')->default(false);
            $table->boolean('randomize_options')->default(false);
            $table->boolean('show_score_after_submit')->default(false);
            $table->enum('status', ['draft', 'published', 'finished'])->default('draft');
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('subject_id');
            $table->index('status');
            $table->index('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
