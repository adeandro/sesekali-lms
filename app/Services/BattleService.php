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

    public function getState(BattleRoom $room): array
    {
        $key = $room->cacheKey('state');
        return Cache::get($key, [
            'state'   => 'lobby',
            'q_index' => 0,
            'q_total' => $room->total_questions,
            'room_id' => $room->id,
            'mode'    => $room->mode,
        ]);
    }

    public function setState(
        BattleRoom $room,
        string $state,
        array $extra = []
    ): array {
        $current = $this->getState($room);
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
            'avatar_url'  => $participant->user->photo_url,
            'group_label' => $participant->group_label,
            'joined_at'   => now()->timestamp,
        ];

        Cache::put($key, $members, self::TTL);
    }

    public function getMembers(BattleRoom $room): array
    {
        return Cache::get($room->cacheKey('members'), []);
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
            'answer'       => $answer,
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
        $groups = [];

        foreach ($scores as $score) {
            $label = $score['group_label'] ?? 'Tanpa Grup';
            if (!isset($groups[$label])) {
                $groups[$label] = [
                    'label'       => $label,
                    'total_score' => 0,
                    'members'     => 0,
                ];
            }
            $groups[$label]['total_score'] +=
                $score['total_score'];
            $groups[$label]['members']++;
        }

        return collect($groups)
            ->sortByDesc('total_score')
            ->values()
            ->toArray();
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
