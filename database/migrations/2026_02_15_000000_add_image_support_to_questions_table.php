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
        Schema::table('questions', function (Blueprint $table) {
            // Add image column for question itself
            $table->string('question_image')->nullable()->after('question_text');

            // Add image columns for each option
            $table->string('option_a_image')->nullable()->after('option_a');
            $table->string('option_b_image')->nullable()->after('option_b');
            $table->string('option_c_image')->nullable()->after('option_c');
            $table->string('option_d_image')->nullable()->after('option_d');
            $table->string('option_e_image')->nullable()->after('option_e');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'question_image',
                'option_a_image',
                'option_b_image',
                'option_c_image',
                'option_d_image',
                'option_e_image',
            ]);
        });
    }
};
