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
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')
                  ->constrained('letter_templates')
                  ->cascadeOnDelete();
            $table->string('letter_number'); // 050/SMK-TN/II/2026
            $table->integer('sequence_number'); // nomor urut
            $table->year('year'); // untuk tracking reset
            $table->enum('recipient_type', ['student', 'teacher', 'custom']);
            $table->foreignId('recipient_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->string('recipient_name'); // nama penerima
            $table->longText('body_rendered'); // HTML final
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->date('issued_date');
            $table->timestamps();
            $table->index(['year', 'sequence_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
