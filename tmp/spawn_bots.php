<?php

/**
 * SESEKALI CBT - BATTLE ARENA V2 
 * PURE CACHE-BASED STRESS TEST (Safe for Production)
 * Usage: php tmp/spawn_bots.php TOKEN [COUNT]
 */

use App\Models\BattleRoom;
use App\Services\BattleService;
use Illuminate\Support\Facades\Cache;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$battleService = app(BattleService::class);

$token = $argv[1] ?? 'A2M8ED';
$count = (int)($argv[2] ?? 100);

$room = BattleRoom::where('token', $token)->first();

if (!$room) {
    die("Error: Room dengan token {$token} tidak ditemukan!\n");
}

echo "--- Spawning {$count} Virtual Bots for Room: {$token} ---\n";
echo "Note: Menggunakan metode Cache-Only (Aman dari Error Database Schema)\n";

$members = [];
$scores = [];

for ($i = 1; $i <= $count; $i++) {
    $uid = 9000 + $i;
    
    // Virtual Member Data
    $members[$uid] = [
        'id'           => $uid,
        'user_id'      => $uid,
        'name'         => "Student Bot #$i",
        'avatar_url'   => "https://api.dicebear.com/7.x/pixel-art/svg?seed=Bot$i",
        'group_label'  => null,
        'is_virtual'   => true
    ];

    // Virtual Score Data
    $scores[$uid] = [
        'user_id'     => $uid,
        'name'        => "Student Bot #$i",
        'avatar_url'  => "https://api.dicebear.com/7.x/pixel-art/svg?seed=Bot$i",
        'total_score' => rand(100, 2000),
        'correct'     => rand(5, 10),
        'wrong'       => rand(0, 5),
        'streak'      => rand(0, 3),
        'rank'        => $i,
        'group_label' => null,
    ];
}

// Inject ke Cache
Cache::put($room->cacheKey('members'), $members, 3600);
Cache::put($room->cacheKey('scores'), $scores, 3600);

// Force Mirror Sync
echo "Syncing Static Mirror JSON...\n";
$battleService->syncStaticMirror($room);

echo "--- SUCCESS ---\n";
echo "Room {$token} sekarang memiliki {$count} peserta virtual di Cache.\n";
echo "Silakan cek halaman display/control Anda.\n";
echo "Tips: Untuk menghapus bot ini, cukup klik 'Reset' atau Hapus Room.\n";
