<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('period_id')->nullable();
            $table->foreign('period_id')->references('id')->on('election_periods')->nullOnDelete();
            $table->string('action', 50);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // NO updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_audit_logs');
    }
};
