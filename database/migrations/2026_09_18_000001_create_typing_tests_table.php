<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('typing_tests', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->string('token', 50)->nullable();
            $table->integer('duration_seconds')->default(60);
            $table->integer('target_wpm')->default(30);
            $table->integer('weight_accuracy')->default(60);
            $table->integer('weight_speed')->default(40);
            $table->enum('word_category', ['mixed'])->default('mixed');
            $table->integer('word_count')->default(200);
            $table->boolean('show_result')->default(true);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->json('class_restriction')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('typing_tests');
    }
};
