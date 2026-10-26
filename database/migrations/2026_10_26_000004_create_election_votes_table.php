<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_votes', function (Blueprint $table) {
            // UUID v4 acak (Str::uuid()), bukan auto-increment
            $table->uuid('id')->primary();
            $table->foreignId('period_id')->constrained('election_periods')->cascadeOnDelete();

            // NULL = suara kosong (blank vote)
            $table->unsignedBigInteger('candidate_id')->nullable();
            $table->foreign('candidate_id')->references('id')->on('election_candidates')->cascadeOnDelete();

            // NO timestamps, NO kolom yang menghubungkan suara ke siswa

            $table->index(['period_id', 'candidate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_votes');
    }
};
