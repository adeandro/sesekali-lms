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
        \DB::statement("ALTER TABLE subjects
            MODIFY COLUMN category
            ENUM('umum','kejuruan','muatan_sekolah','pilihan')
            DEFAULT 'umum'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE subjects
            MODIFY COLUMN category
            ENUM('umum','kejuruan','muatan_sekolah')
            DEFAULT 'umum'");
    }
};
