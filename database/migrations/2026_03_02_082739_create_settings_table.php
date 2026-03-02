<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $row) {
            $row->id();
            $row->string('key')->unique();
            $row->text('value')->nullable();
            $row->timestamps();
        });

        // Default values
        DB::table('settings')->insert([
            ['key' => 'school_name', 'value' => 'ExamFlow', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'logo', 'value' => null, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'max_violations', 'value' => '3', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'anti_cheat_active', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'academic_year', 'value' => '2023/2024', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
