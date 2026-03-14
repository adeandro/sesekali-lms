<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');          // e.g. "XI-IPA-1" — display name
            $table->string('grade');         // e.g. "10", "11", "12"
            $table->string('section')->nullable(); // e.g. "IPA-1", "A", "B"
            $table->string('academic_year', 9);    // e.g. "2025/2026"
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->timestamps();

            $table->unique(['grade', 'section', 'academic_year'], 'unique_class');
            $table->index(['grade', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
