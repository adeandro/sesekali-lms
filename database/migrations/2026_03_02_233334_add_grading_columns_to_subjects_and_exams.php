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
            $table->integer('kkm')->default(75)->after('name');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->integer('weight_pg')->default(70)->after('allow_review_results');
            $table->integer('weight_essay')->default(30)->after('weight_pg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('kkm');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['weight_pg', 'weight_essay']);
        });
    }
};
