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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nis')->unique()->nullable()->after('email');
            $table->string('grade')->nullable()->after('nis')->comment('Grade level: 10, 11, 12');
            $table->string('class_group')->nullable()->after('grade')->comment('Class group: A, B, C, etc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nis', 'grade', 'class_group']);
        });
    }
};
