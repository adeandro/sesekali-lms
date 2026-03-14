<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('migration_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('action_type', ['promote', 'graduate', 'remap']);
            $table->foreignId('executed_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('affected_count')->default(0);
            $table->string('academic_year', 9);          // e.g. "2025/2026"
            $table->json('notes')->nullable();            // extra details: stay_behind_ids, remap_errors, etc.
            $table->timestamp('executed_at')->useCurrent();
            $table->timestamps();

            $table->index(['action_type', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('migration_logs');
    }
};
