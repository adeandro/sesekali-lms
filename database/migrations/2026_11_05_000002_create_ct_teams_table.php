<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ct_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ct_class_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('number');       // team number within class

            $table->json('params');                       // CaseGenerator params (deterministic)
            $table->string('code_hash');                  // HMAC-SHA256(4-digit code, APP_KEY)
            $table->unsignedInteger('wrong_count')->default(0);
            $table->unsignedTinyInteger('hint_count')->default(0);
            $table->dateTime('cooldown_until')->nullable();
            $table->dateTime('opened_at')->nullable();

            $table->json('member_names')->nullable();     // locked after opened
            $table->string('certificate_no')->nullable(); // CT-YYMM-XXXXXXXX

            $table->timestamps();

            // A team number must be unique within its class
            $table->unique(['ct_class_id', 'number']);
            // Codes must be globally unique (HMAC ensures we can verify uniqueness)
            $table->unique('code_hash');
            // For looking up certificate_no quickly
            $table->unique('certificate_no');
            $table->index('opened_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ct_teams');
    }
};
