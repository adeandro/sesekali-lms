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
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Surat Keterangan Siswa Aktif
            $table->string('code', 20); // SKS, SIP, SPA, dll
            $table->enum('category', ['siswa', 'guru', 'custom']);
            $table->longText('body'); // HTML template dengan placeholder
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letter_templates');
    }
};
