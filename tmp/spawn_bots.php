<?php

/**
 * SESEKALI CBT - BATTLE ARENA V2 
 * STRESS TEST BOT SPAWNER
 * Usage: php tmp/spawn_bots.php TOKEN [COUNT]
 */

use App\Models\BattleRoom;
use App\Services\BattleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$battleService = app(BattleService::class);

$token = $argv[1] ?? 'A2M8ED';
$count = $argv[2] ?? 100;

$room = BattleRoom::where('token', $token)->first();

if (!$room) {
    die("Error: Room dengan token {$token} tidak ditemukan!\n");
}

echo "--- Spawning {$count} Bots for Room: {$token} ---\n";

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

$inserted = 0;
for ($i = 1; $i <= $count; $i++) {
    $uid = 9000 + $i; // Offset virtual user ID
    
    $exists = DB::table('battle_participants')->where([
        'battle_room_id' => $room->id,
        'user_id' => $uid
    ])->exists();

    if ($exists) continue;

    DB::table('battle_participants')->insert([
        'battle_room_id' => $room->id,
        'user_id' => $uid,
        'hp' => 100,
        'status' => 'active',
        'joined_at' => now(),
        'last_seen_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $inserted++;
}

DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "Inserted {$inserted} new participants in DB.\n";

// Re-initialize cache with new members
echo "Re-initializing cache scores...\n";
$battleService->initScores($room);

// Force Mirror Sync
echo "Syncing Static Mirror JSON...\n";
$battleService->syncStaticMirror($room);

echo "--- SUCCESS ---\n";
echo "Room {$token} has " . $room->participants()->count() . " total participants.\n";
echo "Silakan cek halaman display/control Anda.\n";
