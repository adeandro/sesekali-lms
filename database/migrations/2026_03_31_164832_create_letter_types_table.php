<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seeder data
        $types = [
            ['name' => 'Surat Keputusan', 'code' => 'SK'],
            ['name' => 'Surat Ketetapan', 'code' => 'Tap'],
            ['name' => 'Surat Edaran', 'code' => 'SEd'],
            ['name' => 'Surat Keterangan', 'code' => 'Ket'],
            ['name' => 'Surat Perintah', 'code' => 'Per'],
            ['name' => 'Surat Tugas', 'code' => 'ST'],
            ['name' => 'Surat Peringatan', 'code' => 'SP'],
            ['name' => 'Surat Perjanjian', 'code' => 'SPn'],
            ['name' => 'Surat Permohonan', 'code' => 'Req'],
            ['name' => 'Surat Pengantar', 'code' => 'SPeng'],
            ['name' => 'Memorandum', 'code' => 'Mem'],
            ['name' => 'Nota Dinas', 'code' => 'ND'],
            ['name' => 'Pengumuman', 'code' => 'Peng'],
            ['name' => 'Surat Kuasa', 'code' => 'SKu'],
            ['name' => 'Surat Undangan', 'code' => 'Und'],
            ['name' => 'Surat Ijin Kegiatan', 'code' => 'SIK'],
            ['name' => 'Surat Lain', 'code' => 'L'],
        ];

        foreach ($types as $index => $type) {
            DB::table('letter_types')->insert([
                'name' => $type['name'],
                'code' => $type['code'],
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_types');
    }
};
