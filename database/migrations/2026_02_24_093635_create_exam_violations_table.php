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
        Schema::create('exam_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('violation_type', [
                'tab_switch',
                'fullscreen_exit',
                'keyboard_shortcut',
                'right_click',
                'copy_paste',
                'dev_tools',
                'printscreen'
            ])->default('tab_switch');
            $table->string('description')->nullable();
            $table->integer('violation_count')->default(1);
            $table->timestamp('detected_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_violations');
    }
};
