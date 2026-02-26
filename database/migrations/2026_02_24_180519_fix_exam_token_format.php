<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix token format from possible double-dash (XXXX--XXXX) to single-dash (XXXX-XXXX)
     */
    public function up(): void
    {
        // Find and fix tokens with double dashes
        $tokens = DB::table('exam_tokens')->where('token', 'like', '%--% ')->get();

        foreach ($tokens as $token) {
            // Replace double dash with single dash
            $fixedToken = str_replace('--', '-', $token->token);

            // Update the token
            DB::table('exam_tokens')
                ->where('id', $token->id)
                ->update(['token' => $fixedToken]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down - tokens are fixed in place
    }
};
