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
        Schema::table('seasons', function (Blueprint $table) {
            $table->enum('semester_type', ['ganjil', 'genap'])->after('name')->nullable();
            $table->string('academic_year', 9)->after('semester_type')->nullable(); // e.g. "2025/2026"
            $table->boolean('migration_done')->after('closed_by')->default(false);
            $table->timestamp('migration_executed_at')->after('migration_done')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn(['semester_type', 'academic_year', 'migration_done', 'migration_executed_at']);
        });
    }
};
