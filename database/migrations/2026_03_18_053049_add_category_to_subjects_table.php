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
            $table->enum('category', [
                'umum', 'kejuruan', 'muatan_sekolah'
            ])->default('umum')->nullable()->after('name');

            $table->tinyInteger('sort_order')
                  ->unsigned()->default(0)->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['category', 'sort_order']);
        });
    }
};
