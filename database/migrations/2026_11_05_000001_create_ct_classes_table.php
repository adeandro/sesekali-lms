<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ct_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();           // e.g. "10-A"
            $table->dateTime('opens_at')->nullable();
            $table->dateTime('closes_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index(['opens_at', 'closes_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ct_classes');
    }
};
