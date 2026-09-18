<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('typing_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('typing_test_id')->constrained('typing_tests')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->text('words_generated');
            $table->text('words_typed')->nullable();
            $table->integer('characters_correct')->default(0);
            $table->integer('characters_wrong')->default(0);
            $table->integer('characters_total')->default(0);
            $table->integer('words_correct')->default(0);
            $table->decimal('wpm', 8, 2)->nullable();
            $table->decimal('accuracy', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['typing_test_id', 'student_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('typing_attempts');
    }
};
