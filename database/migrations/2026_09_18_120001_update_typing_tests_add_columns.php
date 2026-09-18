<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('typing_tests', function (Blueprint $table) {
            // Hapus kolom lama show_result
            $table->dropColumn('show_result');

            // Tambah use_token setelah description
            $table->boolean('use_token')
                ->default(false)
                ->after('description')
                ->comment('Aktifkan token akses');

            // Tambah show_wpm_accuracy & show_score setelah weight_speed
            $table->boolean('show_wpm_accuracy')
                ->default(true)
                ->after('weight_speed')
                ->comment('Tampilkan WPM dan Akurasi ke siswa');

            $table->boolean('show_score')
                ->default(true)
                ->after('show_wpm_accuracy')
                ->comment('Tampilkan nilai akhir ke siswa');
        });
    }

    public function down(): void
    {
        Schema::table('typing_tests', function (Blueprint $table) {
            $table->dropColumn(['show_wpm_accuracy', 'show_score', 'use_token']);
            $table->boolean('show_result')->default(true);
        });
    }
};
