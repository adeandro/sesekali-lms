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
        // 1. Temporarily allow both 'admin' and 'teacher' (and others) to facilitate migration
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'admin', 'teacher', 'student') NOT NULL DEFAULT 'student'");
        }

        // 2. Migrate data: admin -> teacher
        DB::table('users')->where('role', 'admin')->update(['role' => 'teacher']);

        // 3. Finalize enum: remove 'admin'
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'teacher', 'student') NOT NULL DEFAULT 'student'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'admin', 'student') NOT NULL DEFAULT 'student'");
        }

        DB::table('users')->where('role', 'teacher')->update(['role' => 'admin']);
    }
};
