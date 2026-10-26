<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_voters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('election_periods')->cascadeOnDelete();

            // Snapshot — referensi ke user boleh null jika siswa dihapus
            $table->unsignedBigInteger('student_id')->nullable();
            $table->foreign('student_id')->references('id')->on('users')->nullOnDelete();
            $table->string('nis', 30);
            $table->string('student_name', 191);
            $table->unsignedBigInteger('class_id')->nullable(); // tanpa FK agar bebas migrate
            $table->string('class_name', 100)->nullable();

            // Token
            $table->char('token_hash', 64)->nullable(); // sha256 hex
            $table->unsignedInteger('token_version')->default(0);
            $table->timestamp('token_printed_at')->nullable();

            // Voting state
            $table->boolean('has_voted')->default(false);
            $table->timestamp('voted_at')->nullable(); // dibulatkan ke jam

            // NO created_at / updated_at
            $table->unique(['period_id', 'nis']);
            $table->index(['period_id', 'class_id']);
            $table->index(['period_id', 'has_voted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_voters');
    }
};
