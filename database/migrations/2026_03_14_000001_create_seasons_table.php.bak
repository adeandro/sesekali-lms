<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // e.g. "Semester Ganjil 2025/2026"
            $table->enum('semester_type', ['ganjil', 'genap']);
            $table->string('academic_year', 9);              // e.g. "2025/2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->boolean('reset_done')->default(false);   // true after seasonal_exp reset ran
            $table->boolean('migration_done')->default(false); // true after grade migration ran
            $table->timestamp('reset_executed_at')->nullable();
            $table->timestamp('migration_executed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
