<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            if (!Schema::hasColumn('achievements', 'icon_type')) {
                $table->string('icon_type')->default('fa')->after('icon');
            }
            if (!Schema::hasColumn('achievements', 'theme_color')) {
                $table->string('theme_color')->nullable()->after('color');
            }
            if (!Schema::hasColumn('achievements', 'glow_color')) {
                $table->string('glow_color')->nullable()->after('theme_color');
            }
            if (!Schema::hasColumn('achievements', 'criteria_data')) {
                $table->json('criteria_data')->nullable()->after('criteria_value');
            }
            if (!Schema::hasColumn('achievements', 'is_secret')) {
                $table->boolean('is_secret')->default(false)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $table->dropColumn(['icon_type', 'theme_color', 'glow_color', 'criteria_data', 'is_secret']);
        });
    }
};
