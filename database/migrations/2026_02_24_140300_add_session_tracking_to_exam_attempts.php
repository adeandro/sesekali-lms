<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            // Safely drop unique constraint on session_id
            try {
                $table->dropUnique(['session_id']);
            } catch (\Exception $e) {
                // Constraint doesn't exist, continue
            }
            
            // Safely drop regular index on heartbeat_last_seen
            try {
                $table->dropIndex(['heartbeat_last_seen']);
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
            
            // Drop all columns (only drop if they exist)
            if (Schema::hasColumn('exam_attempts', 'session_id')) {
                $table->dropColumn('session_id');
            }
            if (Schema::hasColumn('exam_attempts', 'token')) {
                $table->dropColumn('token');
            }
            if (Schema::hasColumn('exam_attempts', 'heartbeat_last_seen')) {
                $table->dropColumn('heartbeat_last_seen');
            }
            if (Schema::hasColumn('exam_attempts', 'is_session_locked')) {
                $table->dropColumn('is_session_locked');
            }
            if (Schema::hasColumn('exam_attempts', 'force_submitted')) {
                $table->dropColumn('force_submitted');
            }
            if (Schema::hasColumn('exam_attempts', 'force_submit_reason')) {
                $table->dropColumn('force_submit_reason');
            }
            if (Schema::hasColumn('exam_attempts', 'force_submitted_at')) {
                $table->dropColumn('force_submitted_at');
            }
        });
    }
};

