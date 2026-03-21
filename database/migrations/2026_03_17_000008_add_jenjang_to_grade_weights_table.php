<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom jenjang yang tidak masuk DB karena migration 000004
        // sudah dijalankan sebelum kolom ini ditambahkan ke file migration.
        //
        // NOTE: Unique constraint (unique_weight_per_period) tidak diubah di
        // sini karena MySQL melarang DROP INDEX yang digunakan oleh FK constraint.
        // Validasi duplikat per jenjang dilakukan di application level
        // (GradeWeightController).
        Schema::table('grade_weights', function (Blueprint $table) {
            if (!Schema::hasColumn('grade_weights', 'jenjang')) {
                $table->tinyInteger('jenjang')->unsigned()->nullable()->after('subject_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grade_weights', function (Blueprint $table) {
            if (Schema::hasColumn('grade_weights', 'jenjang')) {
                $table->dropColumn('jenjang');
            }
        });
    }
};
