<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subject_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Migrate existing data
        $usersWithSubject = DB::table('users')->whereNotNull('subject_id')->select('id', 'subject_id')->get();
        foreach ($usersWithSubject as $user) {
            DB::table('subject_user')->insert([
                'user_id' => $user->id,
                'subject_id' => $user->subject_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop subject_id from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
        });

        // Migrate data back (take the first subject from pivot)
        $pivotData = DB::table('subject_user')->select('user_id', 'subject_id')->get()->groupBy('user_id');
        foreach ($pivotData as $userId => $subjects) {
            DB::table('users')->where('id', $userId)->update([
                'subject_id' => $subjects->first()->subject_id
            ]);
        }

        Schema::dropIfExists('subject_user');
    }
};
