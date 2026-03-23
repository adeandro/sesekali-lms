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
        Schema::table('subjects', function (Blueprint $table) {
            // JSON array: ['X','XI','XII'] atau null = semua jenjang
            $table->json('active_grades')
                  ->nullable()
                  ->after('sort_order')
                  ->comment('Null = aktif di semua jenjang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('active_grades');
        });
    }
};
