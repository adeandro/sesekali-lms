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
        Schema::create('project_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('project_assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->integer('slot_number')->default(1);
            $table->string('title', 191);
            $table->string('original_filename', 191);
            $table->string('storage_path', 500);
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->boolean('has_css')->default(false);
            $table->boolean('has_js')->default(false);
            $table->boolean('has_images')->default(false);
            $table->boolean('has_audio')->default(false);
            $table->boolean('has_video')->default(false);
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'student_id', 'slot_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_submissions');
    }
};
