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
        $templates = \App\Models\LetterTemplate::all();
        foreach ($templates as $template) {
            $body = $template->body;
            // Replace {{key}} → [key]
            $body = preg_replace('/\{\{([^}]+)\}\}/', '[$1]', $body);
            // Replace PHP echo syntax → [key]
            $body = preg_replace(
                '/&lt;\?php echo e\(([^)]+)\); \?&gt;/', 
                '[$1]', 
                $body
            );
            $body = preg_replace(
                '/<\?php echo e\(([^)]+)\); \?>/', 
                '[$1]', 
                $body
            );
            $template->update(['body' => $body]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak bisa di-reverse secara otomatis
    }
};
