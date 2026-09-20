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
        Schema::create('project_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->string('slug', 191)->unique();
            $table->text('description')->nullable();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->integer('max_file_size_mb')->default(10);
            $table->integer('max_slots')->default(1);
            $table->json('class_restriction')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_assignments');
    }
};
