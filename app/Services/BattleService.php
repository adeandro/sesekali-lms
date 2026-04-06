<?php
namespace App\Services;

use App\Models\BattleRoom;
use App\Models\BattleParticipant;
use App\Models\Question;
use Illuminate\Support\Facades\Cache;

class BattleService
{
    const VALID_STATES = [
        'lobby', 'preview', 'question',
        'discussion', 'leaderboard', 'finish'
    ];

    const TTL = 4 * 60 * 60; // 4 jam

    // ── State Management ─────────────────────

    public function getState(BattleRoom $room, bool $autoAdvance = true): array
    {
        $key = $room->cacheKey('state');
        $cached = Cache::get($key, [
            'state'   => 'lobby',
            'q_index' => 0,
            'q_total' => $room->total_questions,
            'room_id' => $room->id,
            'mode'    => $room->mode,
        ]);

        // Ensure mode is always present (for older caches)
        if (!isset($cached['mode'])) {
            $cached['mode'] = $room->mode;
        }

        // ── AUTO ADVANCE: Question -> Discussion ──
        if ($autoAdvance && ($cached['state'] ?? '') === 'question') {
            $started = $cached['question_started_at'] ?? 0;
            $duration = $cached['question_duration'] ?? $room->duration_per_question;
            
            // Beri grace period 1 detik untuk network latency
            if ($started > 0 && (now()->timestamp > ($started + $duration + 1))) {
                return $this->setState($room, 'discussion');
            }
        }

        return $cached;
    }

    public function setState(
        BattleRoom $room,
        string $state,
        array $extra = []
    ): array {
        // Jangan auto-advance saat sedang di-set manual (hindari rekursi)
        $current = $this->getState($room, false);
        $new = array_merge($current, $extra, [
            'state'      => $state,
            'updated_at' => now()->timestamp,
        ]);

        // Jika masuk state QUESTION, catat timestamp
        if ($state === 'question') {
            $new['question_started_at'] = now()->timestamp;
            $new['question_duration']   =
                $room->duration_per_question;
        }

        Cache::put($room->cacheKey('state'),
            $new, self::TTL);
        
        $this->syncStaticMirror($room, $new);

        return $new;
    }

    public function nextQuestion(
        BattleRoom $room
    ): array {
        $state   = $this->getState($room);
        $current = $state['q_index'] ?? 0;
        $total   = $room->total_questions;

        if ($current + 1 >= $total) {
            // Semua soal selesai → finish
            return $this->setState($room, 'finish', [
                'q_index' => $current,
            ]);
        }

        // Reset jawaban soal ini
        Cache::forget($room->cacheKey('answers'));

        return $this->setState($room, 'preview', [
            'q_index' => $current + 1,
        ]);
    }

    public function syncStaticMirror(BattleRoom $room, ?array $state = null): void
    {
        if (!$state) {
            $state = $this->getState($room, false);
        }

        $members = $this->getMembers($room);
        $scores = $this->getScores($room);
        $groupScores = ($room->mode === 'group') ? $this->getGroupScores($room) : [];
        
        $qIndex = $state['q_index'] ?? 0;
        $question = Cache::get("battle:{$room->token}:q:{$qIndex}");

        // Sanitasi data soal: hanya tampilkan jawaban saat diskusi
        if ($question && ($state['state'] ?? '') !== 'discussion') {
            unset($question['correct_answer']);
            unset($question['explanation']);
        }

        $stats = [];
        if (($state['state'] ?? '') === 'discussion') {
            $stats = $this->getAnswerStats($room, ['a', 'b', 'c', 'd', 'e']);
        }

        $currentState = $state['state'] ?? '';
        $isLobby = ($currentState === 'lobby');
        $isQuestion = in_array($currentState, ['preview', 'question']);
        $isDiscussion = ($currentState === 'discussion');
        $isLeaderboard = ($currentState === 'leaderboard');

        // Payload Pruning (Bandwidth Hub)
        $membersToSync = [];
        $scoresToSync = [];

        if ($isLobby) {
            // Lobby butuh list nama lengkap untuk daftar hadir
            $membersToSync = array_values($members);
        } elseif ($isQuestion) {
            // Saat soal berlangsung, SEMUA list anggota & skor dihapus dari pengiriman
            // (Siswa tidak perlu data ini saat sedang countdown)
            $membersToSync = [];
            $scoresToSync = [];
        } elseif ($isDiscussion) {
            // Saat pembahasan, kirim top 5 skor saja untuk leaderboard kecil
            $scoresToSync = array_slice(array_values($scores), 0, 5); 
            $membersToSync = []; // Nama dikaitkan via ID jika sudah ada di cache lokal
        } elseif ($isLeaderboard) {
            // Saat podium, butuh list skor penuh dan nama
            $scoresToSync = array_values($scores);
            $membersToSync = array_values($members);
        }

        $mirrorData = [
            'room_id'        => $room->id,
            'token'          => $room->token,
            'state'          => $state,
            'member_count'   => count($members),
            'members'        => $membersToSync,
            'scores'         => $scoresToSync,
            'group_scores'   => $groupScores,
            'question'       => $question,
            'stats'          => $stats,
            'is_locked'      => (bool) $room->is_locked,
            'show_on_device' => (bool) $room->show_question_on_device,
            'updated_at'     => now()->timestamp,
        ];

        $path = public_path("battle-mirror/{$room->token}.json");
        
        // Buat folder jika belum ada (antisipasi pertama kali)
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($mirrorData, JSON_UNESCAPED_UNICODE));
    }

    // ── Member Management ────────────────────

    public function addMember(
        BattleRoom $room,
        BattleParticipant $participant
    ): void {
        $key     = $room->cacheKey('members');
        $members = Cache::get($key, []);

        $members[$participant->user_id] = [
            'id'          => $participant->id,
            'user_id'     => $participant->user_id,
            'name'        => $participant->user->name,
            'initials'    => $participant->user->initials,
            'avatar_url'  => $participant->user->avatar_url,
            'is_avatar_seed'=> $participant->user->is_avatar_seed,
            'avatar_seed' => $participant->user->avatar_seed,
            'group_label' => $participant->group_label,
            'joined_at'   => now()->timestamp,
        ];

        Cache::put($key, $members, self::TTL);
        $this->syncStaticMirror($room);
    }

    public function getMembers(BattleRoom $room): array
    {
        return Cache::get($room->cacheKey('members'), []);
    }

    public function updateMemberGroup(
        BattleRoom $room,
        int $userId,
        string $groupLabel
    ): void {
        $key     = $room->cacheKey('members');
        $members = Cache::get($key, []);

        if (isset($members[$userId])) {
            $members[$userId]['group_label'] = $groupLabel;
            Cache::put($key, $members, self::TTL);
        }

        // Juga update scores cache jika sudah ada
        $scoreKey = $room->cacheKey('scores');
        $scores   = Cache::get($scoreKey, []);
        if (isset($scores[$userId])) {
            $scores[$userId]['group_label'] = $groupLabel;
            Cache::put($scoreKey, $scores, self::TTL);
        }

        $this->syncStaticMirror($room);
    }

    // ── Score Management ─────────────────────

    public function initScores(BattleRoom $room): void
    {
        $members = $this->getMembers($room);
        $scores  = [];

        foreach ($members as $userId => $member) {
            $scores[$userId] = [
                'user_id'     => $userId,
                'name'        => $member['name'],
                'avatar_url'  => $member['avatar_url'],
                'is_avatar_seed'=> $member['is_avatar_seed'] ?? false,
                'avatar_seed' => $member['avatar_seed'] ?? null,
                'group_label' => $member['group_label'],
                'total_score' => 0,
                'correct'     => 0,
                'wrong'       => 0,
                'streak'      => 0,
                'rank'        => 0,
            ];
        }

        Cache::put($room->cacheKey('scores'),
            $scores, self::TTL);
    }

    public function getScores(BattleRoom $room): array
    {
        return Cache::get(
            $room->cacheKey('scores'), []
        );
    }

    public function calculateScore(
        bool $isCorrect,
        int $timeRemaining,
        int $totalDuration,
        int $streak
    ): int {
        if (!$isCorrect) return 0;

        $base        = 500;
        $speedBonus  = (int) round(
            ($timeRemaining / $totalDuration) * 300
        );
        $streakBonus = min($streak * 50, 200);

        return $base + $speedBonus + $streakBonus;
    }

    public function updateScore(
        BattleRoom $room,
        int $userId,
        bool $isCorrect,
        int $scoreEarned
    ): void {
        $key    = $room->cacheKey('scores');
        $scores = Cache::get($key, []);

        if (!isset($scores[$userId])) return;

        if ($isCorrect) {
            $scores[$userId]['total_score'] += $scoreEarned;
            $scores[$userId]['correct']++;
            $scores[$userId]['streak']++;
        } else {
            $scores[$userId]['wrong']++;
            $scores[$userId]['streak'] = 0;
        }

        // Recalculate ranks
        $sorted = collect($scores)
            ->sortByDesc('total_score')
            ->values();

        foreach ($sorted as $i => $s) {
            $scores[$s['user_id']]['rank'] = $i + 1;
        }

        Cache::put($key, $scores, self::TTL);
    }

    // ── Answer Management ────────────────────

    public function recordAnswer(
        BattleRoom $room,
        int $userId,
        string $answer,
        bool $isCorrect,
        int $scoreEarned
    ): void {
        $key     = $room->cacheKey('answers');
        $answers = Cache::get($key, []);

        $answers[$userId] = [
            'answer'       => $answer === 'none' ? null : $answer,
            'is_correct'   => $isCorrect,
            'score_earned' => $scoreEarned,
            'answered_at'  => now()->timestamp,
        ];

        Cache::put($key, $answers, self::TTL);
    }

    public function getAnswers(BattleRoom $room): array
    {
        return Cache::get(
            $room->cacheKey('answers'), []
        );
    }

    public function hasAnswered(
        BattleRoom $room,
        int $userId
    ): bool {
        $answers = $this->getAnswers($room);
        return isset($answers[$userId]);
    }

    public function getAnswerStats(
        BattleRoom $room,
        array $options
    ): array {
        $answers = $this->getAnswers($room);
        $total   = count($answers);
        $stats   = [];

        foreach ($options as $opt) {
            $count = collect($answers)
                ->where('answer', $opt)
                ->count();
            $stats[$opt] = [
                'count'   => $count,
                'percent' => $total > 0
                    ? round(($count / $total) * 100)
                    : 0,
            ];
        }

        return $stats;
    }

    // ── Group Scoring ────────────────────────

    public function getGroupScores(
        BattleRoom $room
    ): array {
        $scores = $this->getScores($room);
        $members = $this->getMembers($room);
        
        // Gunakan group_names dari room sebagai base agar grup kosong tetap tampil
        $groupLabels = $room->group_names ?? [];
        if (empty($groupLabels) && $room->group_count > 0) {
            for ($i = 1; $i <= $room->group_count; $i++) {
                $groupLabels[] = "Grup " . $i;
            }
        }
        if (empty($groupLabels)) {
            $groupLabels = ['Merah', 'Biru'];
        }
        $groups = [];
        foreach ($groupLabels as $label) {
            $groups[$label] = [
                'group_label' => $label,
                'name'        => $label, // Alias for frontend compatibility
                'total_score' => 0,
                'members'     => 0,
                'top_contributors' => [],
            ];
        }

        // Urutkan scores untuk ambil kontributor tertinggi
        $sortedScores = collect($scores)->sortByDesc('total_score');

        foreach ($scores as $score) {
            $label = $score['group_label'];
            if (!$label || !isset($groups[$label])) continue;
            
            $groups[$label]['total_score'] += $score['total_score'];
            $groups[$label]['members']++;
        }

        // Ambil 3 kontributor teratas untuk tiap grup
        foreach ($groups as $label => &$group) {
            $group['top_contributors'] = $sortedScores
                ->where('group_label', $label)
                ->take(3)
                ->map(fn($s) => [
                    'name' => $s['name'],
                    'score' => $s['total_score'],
                    'avatar_url' => $s['avatar_url'],
                    'is_avatar_seed' => $s['is_avatar_seed'] ?? false,
                    'avatar_seed' => $s['avatar_seed'] ?? null,
                ])
                ->values()
                ->toArray();
        }

        return collect($groups)->sortByDesc('total_score')->values()->toArray();
    }

    // ── Cleanup ──────────────────────────────

    public function cleanup(BattleRoom $room): void
    {
        foreach (['state', 'scores', 'answers',
                  'members'] as $suffix) {
            Cache::forget($room->cacheKey($suffix));
        }
        // Hapus cache soal
        for ($i = 0; $i < $room->total_questions; $i++) {
            Cache::forget($room->cacheKey('q:' . $i));
        }
    }
}
