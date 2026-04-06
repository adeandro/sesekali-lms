<?php

use App\Models\BattleRoom;
use App\Services\BattleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$battleService = app(BattleService::class);

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

// 1. Create Room (Minimally)
$token = 'PERF'.rand(100,999);
$roomId = DB::table('battle_rooms')->insertGetId([
    'name' => 'Perf Test Room',
    'token' => $token,
    'mode' => 'individual',
    'source_type' => 'exam',
    'source_id' => 1,
    'question_ids' => '[]',
    'total_questions' => 10,
    'status' => 'waiting',
    'is_locked' => 0,
    'created_by' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);
$room = BattleRoom::find($roomId);

echo "--- Room $token Created ---\n";

// 2. Inject 100 Members into Cache
echo "Injecting 100 members into Cache...\n";
$members = [];
$scores = [];
for($i=1; $i<=100; $i++) {
    $uid = 2000 + $i;
    $members[$uid] = [
        'id' => $i,
        'user_id' => $uid,
        'name' => "Student Name #$i",
        'avatar_url' => 'https://ui-avatars.com/api/?name=S'.$i,
        'group_label' => null,
    ];
    $scores[$uid] = [
        'user_id' => $uid,
        'name' => "Student Name #$i",
        'avatar_url' => 'https://ui-avatars.com/api/?name=S'.$i,
        'total_score' => rand(0, 1000),
        'rank' => $i,
        'group_label' => null,
    ];
}

Cache::put($room->cacheKey('members'), $members, 3600);
Cache::put($room->cacheKey('scores'), $scores, 3600);

// 3. Test Lobby State (Full Payload)
echo "Testing Lobby State...\n";
$battleService->setState($room, 'lobby'); 
$path = public_path("battle-mirror/{$token}.json");
$lobbySize = filesize($path) / 1024;
echo "Lobby JSON Size (100 Students): " . number_format($lobbySize, 2) . " KB\n";

// 4. Test Question State (Pruned Payload)
echo "Testing Question State...\n";
$battleService->setState($room, 'question', [
    'q_index' => 0,
    'question_started_at' => time(),
    'question_duration' => 30
]);
$questionSize = filesize($path) / 1024;
echo "Question JSON Size (Pruned): " . number_format($questionSize, 2) . " KB\n";

// 5. Comparison
$diff = $lobbySize - $questionSize;
$percent = ($diff / $lobbySize) * 100;

echo "-----------------------------------\n";
echo "Reduction: " . number_format($diff, 2) . " KB per poll\n";
echo "Percentage: " . number_format($percent, 2) . "% Lighter\n";
echo "-----------------------------------\n";

// Cleanup
DB::table('battle_rooms')->where('id', $roomId)->delete();
if(file_exists($path)) @unlink($path);
$battleService->cleanup($room);

DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "Done.\n";
