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
        Schema::table('project_assignments', function (Blueprint $table) {
            $table->enum('assignment_type', [
                'web',       // ZIP/RAR wajib index.html
                'document',  // PDF, DOCX, PPTX, XLSX
                'image',     // JPG, PNG, GIF, SVG, PSD
                'general',   // File bebas sesuai izin guru
            ])
            ->default('web')
            ->after('slug');

            $table->dropColumn('max_file_size_mb');
        });

        Schema::table('project_assignments', function (Blueprint $table) {
            $table->unsignedInteger('max_file_size_mb')
                ->default(10)
                ->after('assignment_type');

            $table->json('allowed_extensions')
                ->nullable()
                ->after('max_file_size_mb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_assignments', function (Blueprint $table) {
            $table->dropColumn(['assignment_type', 'max_file_size_mb', 'allowed_extensions']);
        });

        Schema::table('project_assignments', function (Blueprint $table) {
            $table->unsignedInteger('max_file_size_mb')
                ->default(10)
                ->after('subject_id');
        });
    }
};
