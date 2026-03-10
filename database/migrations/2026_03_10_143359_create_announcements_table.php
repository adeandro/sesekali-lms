<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // sender
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['info', 'warning', 'urgent'])->default('info');
            $table->enum('target_role', ['all', 'teacher', 'student'])->default('all');
            // Matches the class_group field on the users table (nullable = broadcast to all classes)
            $table->string('target_class_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'target_role', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
