<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            // Rich display fields
            $table->string('title')->nullable()->after('name');
            $table->text('lore_text')->nullable()->after('description');
            $table->string('icon_path')->nullable()->after('icon'); // uploaded file

            // Logic engine fields
            $table->string('criteria_type')->nullable()->after('color');
            $table->string('criteria_value')->nullable()->after('criteria_type');
            $table->boolean('is_active')->default(true)->after('criteria_value');
        });
    }

    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $table->dropColumn(['title', 'lore_text', 'icon_path', 'criteria_type', 'criteria_value', 'is_active']);
        });
    }
};
