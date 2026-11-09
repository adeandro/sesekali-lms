<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::select("
            SELECT period_id, token_hash, COUNT(*) as c
            FROM election_voters
            WHERE token_hash IS NOT NULL
            GROUP BY period_id, token_hash
            HAVING c > 1
        ");

        if (! empty($duplicates)) {
            throw new \RuntimeException('Gagal membuat index unik: Ditemukan duplikat token_hash pada periode yang sama di tabel election_voters.');
        }

        Schema::table('election_voters', function (Blueprint $table) {
            $table->unique(['period_id', 'token_hash']);
        });
    }

    public function down(): void
    {
        Schema::table('election_voters', function (Blueprint $table) {
            $table->dropUnique(['period_id', 'token_hash']);
        });
    }
};
