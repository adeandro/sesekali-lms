<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->text('body');
            $table->string('attachment_path')->nullable();
            $table->boolean('is_read')->default(false);
            // Self-referencing: null = root thread, set = reply in thread
            $table->foreignId('parent_id')->nullable()->constrained('messages')->onDelete('cascade');
            $table->softDeletes(); // Audit trail: Admin can read even "deleted" messages
            $table->timestamps();

            $table->index(['receiver_id', 'is_read']);
            $table->index(['sender_id', 'receiver_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
