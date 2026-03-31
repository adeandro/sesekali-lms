<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_templates', function (Blueprint $table) {
            $table->foreignId('letter_type_id')
                ->nullable()
                ->after('code')
                ->constrained('letter_types')
                ->nullOnDelete();
        });

        Schema::table('letters', function (Blueprint $table) {
            $table->foreignId('letter_type_id')
                ->nullable()
                ->after('template_id')
                ->constrained('letter_types')
                ->nullOnDelete();
            $table->enum('format_type', ['simple', 'with_institution'])
                ->default('simple')
                ->after('letter_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropForeign(['letter_type_id']);
            $table->dropColumn(['letter_type_id', 'format_type']);
        });

        Schema::table('letter_templates', function (Blueprint $table) {
            $table->dropForeign(['letter_type_id']);
            $table->dropColumn('letter_type_id');
        });
    }
};
