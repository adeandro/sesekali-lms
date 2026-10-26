<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_periods', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->date('event_date')->nullable();
            $table->enum('status', ['draft', 'ready', 'open', 'closed'])->default('draft');
            $table->json('eligible_class_ids')->nullable(); // null = semua kelas
            $table->boolean('allow_blank_vote')->default(true);
            $table->string('booth_pin_hash', 255)->nullable();
            $table->unsignedInteger('booth_session_version')->default(1);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->unsignedInteger('collected_cards_count')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_periods');
    }
};
