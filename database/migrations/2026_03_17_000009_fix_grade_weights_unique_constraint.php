<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL tidak bisa DROP INDEX yang digunakan sebagai dasar FK index.
        // Solusi: drop FK dulu → drop index → buat ulang index dengan jenjang → restore FK.
        DB::statement('ALTER TABLE grade_weights DROP FOREIGN KEY grade_weights_subject_id_foreign');
        DB::statement('ALTER TABLE grade_weights DROP FOREIGN KEY grade_weights_teacher_id_foreign');
        DB::statement('ALTER TABLE grade_weights DROP INDEX unique_weight_per_period');
        DB::statement('ALTER TABLE grade_weights ADD UNIQUE unique_weight_per_period (subject_id, teacher_id, jenjang, semester, academic_year)');
        DB::statement('ALTER TABLE grade_weights ADD CONSTRAINT grade_weights_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE grade_weights ADD CONSTRAINT grade_weights_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE grade_weights DROP FOREIGN KEY grade_weights_subject_id_foreign');
        DB::statement('ALTER TABLE grade_weights DROP FOREIGN KEY grade_weights_teacher_id_foreign');
        DB::statement('ALTER TABLE grade_weights DROP INDEX unique_weight_per_period');
        DB::statement('ALTER TABLE grade_weights ADD UNIQUE unique_weight_per_period (subject_id, teacher_id, semester, academic_year)');
        DB::statement('ALTER TABLE grade_weights ADD CONSTRAINT grade_weights_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE grade_weights ADD CONSTRAINT grade_weights_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE');
    }
};
