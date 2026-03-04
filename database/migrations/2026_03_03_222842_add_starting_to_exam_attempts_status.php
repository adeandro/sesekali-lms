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
        DB::statement("ALTER TABLE exam_attempts MODIFY COLUMN status ENUM('in_progress', 'submitted', 'starting') NOT NULL DEFAULT 'in_progress'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE exam_attempts MODIFY COLUMN status ENUM('in_progress', 'submitted') NOT NULL DEFAULT 'in_progress'");
    }
};
