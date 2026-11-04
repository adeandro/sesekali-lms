<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ct_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ct_team_id')->constrained()->cascadeOnDelete();
            $table->json('member_names');                // snapshot of names submitted
            $table->string('submitted_code', 4);         // the 4-digit string submitted
            $table->boolean('is_correct');
            $table->string('ip', 45)->nullable();        // supports IPv6
            $table->timestamp('created_at')->nullable();

            $table->index(['ct_team_id', 'created_at']);
            $table->index('is_correct');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ct_attempts');
    }
};
