<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('election_periods')->cascadeOnDelete();
            $table->unsignedTinyInteger('number');

            // Ketua
            $table->unsignedBigInteger('chairman_id')->nullable();
            $table->foreign('chairman_id')->references('id')->on('users')->nullOnDelete();
            $table->string('chairman_name', 191);
            $table->string('chairman_class', 100)->nullable();

            // Wakil
            $table->unsignedBigInteger('vice_id')->nullable();
            $table->foreign('vice_id')->references('id')->on('users')->nullOnDelete();
            $table->string('vice_name', 191);
            $table->string('vice_class', 100)->nullable();

            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->timestamps();

            $table->unique(['period_id', 'number']);
            $table->unique(['period_id', 'chairman_id']);
            $table->unique(['period_id', 'vice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_candidates');
    }
};
