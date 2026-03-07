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
            $table->decimal('adjusted_score', 8, 2)->nullable()->after('final_score');
            $table->boolean('is_adjusted')->default(false)->after('adjusted_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn(['adjusted_score', 'is_adjusted']);
        });
    }
};
