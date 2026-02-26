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
        Schema::table('exam_attempts', function (Blueprint $table) {
            // Add session tracking to exam_attempts
            $table->string('session_id', 100)->nullable()->unique();
            $table->string('token', 10)->nullable();
            $table->timestamp('heartbeat_last_seen')->nullable()->index();
            $table->boolean('is_session_locked')->default(false);
            $table->boolean('force_submitted')->default(false);
            $table->string('force_submit_reason')->nullable();
            $table->timestamp('force_submitted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropIndex(['session_id']);
            $table->dropIndex(['heartbeat_last_seen']);
            $table->dropColumn([
                'session_id',
                'token',
                'heartbeat_last_seen',
                'is_session_locked',
                'force_submitted',
                'force_submit_reason',
                'force_submitted_at',
            ]);
        });
    }
};
