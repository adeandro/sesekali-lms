<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BattleRoom;
use App\Models\BattleParticipant;
use App\Services\BattleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArenaController extends Controller
{
    public function __construct(
        protected BattleService $battleService
    ) {}

    public function index()
    {
        return view('student.arena.index');
    }

    public function join(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:6',
        ]);

        $room = BattleRoom::where(
            'token', strtoupper($request->token)
        )->first();

        if (!$room) {
            return back()->withErrors([
                'token' => 'Kode room tidak ditemukan.'
            ]);
        }
        if ($room->status === 'finished') {
            return back()->withErrors([
                'token' => 'Battle sudah selesai.'
            ]);
        }
        if ($room->status === 'ongoing') {
            return back()->withErrors([
                'token' => 'Battle sudah berlangsung, tidak bisa join.'
            ]);
        }
        if ($room->is_locked) {
            return back()->withErrors([
                'token' => 'Pendaftaran room sudah dikunci oleh guru.'
            ]);
        }
        if ($room->isFull()) {
            return back()->withErrors([
                'token' => 'Room sudah penuh (maksimal 40 peserta).'
            ]);
        }

        $userId = Auth::id();

        $existing = BattleParticipant::where([
            'battle_room_id' => $room->id,
            'user_id'        => $userId,
        ])->first();

        if ($existing) {
            return redirect()->route(
                'student.arena.lobby', $room->token
            );
        }

        if ($room->mode === 'group') {
            // Sprint 8: Jangan redirect ke pick-group dulu.
            // Biarkan siswa join tanpa grup, lalu tampilkan modal di lobby.
            $groupLabel = null;
        }

        // individual / class — langsung join
        // Untuk class, group_label diisi nama kelas siswa
        $groupLabel = null;
        if ($room->mode === 'class') {
            $user = Auth::user();
            $groupLabel = $user->classroom?->name
                ?? $user->class_group
                ?? 'Kelas';
        }

        $this->doJoin($room, $userId, $groupLabel);

        return redirect()->route(
            'student.arena.lobby', $room->token
        );
    }

    public function pickGroup(BattleRoom $room)
    {
        if ($room->mode !== 'group') {
            return redirect()->route(
                'student.arena.index'
            );
        }

        $existing = BattleParticipant::where([
            'battle_room_id' => $room->id,
            'user_id'        => Auth::id(),
        ])->first();

        if ($existing) {
            return redirect()->route(
                'student.arena.lobby', $room->token
            );
        }

        // Hitung anggota per grup
        $groupCounts = [];
        foreach ($room->group_names as $name) {
            $groupCounts[$name] = BattleParticipant::where([
                'battle_room_id' => $room->id,
                'group_label'    => $name,
            ])->count();
        }

        return view('student.arena.pick-group',
            compact('room', 'groupCounts'));
    }

    public function updateGroup(
        Request $request,
        BattleRoom $room
    ) {
        $request->validate([
            'group_label' => 'required|string',
        ]);

        $groupLabel = $request->group_label;

        if (!in_array($groupLabel, $room->group_names ?? [])) {
            return response()->json(['status' => 'error', 'message' => 'Grup tidak valid.'], 422);
        }

        if ($room->isGroupFull($groupLabel)) {
            return response()->json(['status' => 'error', 'message' => 'Grup ' . $groupLabel . ' sudah penuh.'], 422);
        }

        $userId = Auth::id();
        $participant = BattleParticipant::where([
            'battle_room_id' => $room->id,
            'user_id'        => $userId,
        ])->firstOrFail();

        $participant->update(['group_label' => $groupLabel]);

        // Sync ke Cache (Redis) dan Static Mirror (JSON) agar muncul di lobby teman-temannya
        $this->battleService->updateMemberGroup($room, $userId, $groupLabel);

        return response()->json(['status' => 'ok']);
    }

    public function lobby(BattleRoom $room)
    {
        $participant = BattleParticipant::where([
            'battle_room_id' => $room->id,
            'user_id'        => Auth::id(),
        ])->first();

        if (!$participant) {
            return redirect()
                ->route('student.arena.index')
                ->withErrors([
                    'token' => 'Kamu belum join room ini.'
                ]);
        }

        return view('student.arena.lobby',
            compact('room', 'participant'));
    }

    public function lobbyStatus(BattleRoom $room)
    {
        $state   = $this->battleService->getState($room);
        $members = $this->battleService->getMembers($room);

        return response()->json([
            'state'   => $state['state'],
            'count'   => count($members),
            'members' => array_values($members),
            'is_locked' => (bool)$room->is_locked,
        ]);
    }

    public function battle(BattleRoom $room)
    {
        $participant = BattleParticipant::where([
            'battle_room_id' => $room->id,
            'user_id'        => Auth::id(),
        ])->first();

        if (!$participant) {
            return redirect()->route('student.arena.index');
        }

        return view('student.arena.battle',
            compact('room', 'participant'));
    }

    public function battleData(BattleRoom $room)
    {
        $state  = $this->battleService->getState($room);
        $userId = Auth::id();

        $myScore     = null;
        $hasAnswered = false;

        if (in_array($state['state'] ?? '',
            ['question','discussion','leaderboard','finish'])
        ) {
            $scores      = $this->battleService->getScores($room);
            $myScore     = $scores[$userId] ?? null;
            $hasAnswered = $this->battleService
                ->hasAnswered($room, $userId);

            // Jika siswa belum ada di scores (join telat)
            // inisialisasi entry kosong agar rank tampil
            if ($myScore === null) {
                $user = Auth::user();
                $myScore = [
                    'user_id'     => $userId,
                    'name'        => $user->name,
                    'total_score' => 0,
                    'correct'     => 0,
                    'wrong'       => 0,
                    'streak'      => 0,
                    'rank'        => count($scores) + 1,
                    'group_label' => null,
                ];
            }
        }
        
        // Tambahkan exp_earned berdasarkan rank
        if ($myScore && $state['state'] === 'finish') {
            $rank = $myScore['rank'] ?? 0;
            
            if ($room->mode === 'group') {
                $groupScores = $this->battleService->getGroupScores($room);
                $winningGroup = collect($groupScores)->sortByDesc('total_score')->first()['group_label'] ?? null;
                $rank = ($myScore['group_label'] === $winningGroup) ? 1 : 999;
            }

            $myScore['exp_earned'] = match((int)$rank) {
                1 => $room->reward_rank1_exp,
                2 => $room->reward_rank2_exp,
                3 => $room->reward_rank3_exp,
                default => 50,
            };
            $myScore['physical_reward'] = match((int)$rank) {
                1 => $room->reward_rank1_physical,
                2 => $room->reward_rank2_physical,
                3 => $room->reward_rank3_physical,
                default => null,
            };
        }
        
        $qIndex = $state['q_index'] ?? 0;
        $question = \Illuminate\Support\Facades\Cache::get("battle:{$room->token}:q:{$qIndex}");
        
        // Hide correct answer and explanation if not in discussion
        if ($question && $state['state'] !== 'discussion') {
            unset($question['correct_answer']);
            unset($question['explanation']);
        }

        // Ambil hasil jawaban siswa ini dari Redis
        // (hanya ada saat state discussion)
        $answerResult = null;
        if ($state['state'] === 'discussion') {
            $answers = $this->battleService->getAnswers($room);
            if (isset($answers[$userId])) {
                $answerResult = [
                    'chosen'     => $answers[$userId]['answer'],
                    'is_correct' => $answers[$userId]['is_correct'],
                    'score_earned'=> $answers[$userId]['score_earned'],
                ];
            }
        }

        // Group point accumulation
        $groupScore = null;
        if ($room->mode === 'group' && $myScore && $myScore['group_label']) {
            $groupScores = $this->battleService->getGroupScores($room);
            $myGroup = collect($groupScores)->where('group_label', $myScore['group_label'])->first();
            $groupScore = $myGroup['total_score'] ?? 0;
        }

        return response()->json([
            'state'         => $state,
            'my_score'      => $myScore,
            'group_score'   => $groupScore,
            'has_answered'  => $hasAnswered,
            'question'      => $question,
            'answer_result' => $answerResult,
            'show_question_on_device' => $room->show_question_on_device,
        ]);
    }

    public function submitAnswer(
        Request $request,
        BattleRoom $room
    ) {
        $request->validate([
            'answer'         => 'nullable|in:a,b,c,d,e',
            'time_remaining' => 'required|integer|min:0',
        ]);

        $userId = Auth::id();
        $state  = $this->battleService->getState($room);

        // ── Guard: hanya terima jawaban saat state = question ──
        if (($state['state'] ?? '') !== 'question') {
            return response()->json([
                'status'  => 'rejected',
                'reason'  => 'not_question_state',
            ], 422);
        }

        // ── Guard: timer cut-off ──
        // Hitung sisa waktu di server — jangan percaya client
        $startedAt    = $state['question_started_at'] ?? 0;
        $duration     = $state['question_duration']
                        ?? $room->duration_per_question;
        $serverElapsed = now()->timestamp - $startedAt;

        if ($serverElapsed > $duration + 2) {
            // +2 detik grace period untuk network latency
            return response()->json([
                'status' => 'rejected',
                'reason' => 'time_expired',
            ], 422);
        }

        // ── Guard: idempotency — tolak double submit ──
        if ($this->battleService->hasAnswered($room, $userId)) {
            return response()->json([
                'status' => 'already_answered',
            ]);
        }

        // ── Ambil data soal dari Redis cache ──
        $qIndex     = $state['q_index'] ?? 0;
        $cacheKey   = "battle:{$room->token}:q:{$qIndex}";
        $question   = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if (!$question) {
            return response()->json([
                'status' => 'error',
                'reason' => 'question_not_found',
            ], 500);
        }

        // ── Hitung skor ──
        $answer    = $request->input('answer'); // null = tidak menjawab
        $isCorrect = $answer !== null
                     && strtolower($answer) === strtolower($question['correct_answer']);

        // Gunakan sisa waktu dari server (lebih akurat dari client)
        $serverTimeRemaining = max(0, $duration - $serverElapsed);

        $scores       = $this->battleService->getScores($room);
        $currentStreak = $scores[$userId]['streak'] ?? 0;

        $scoreEarned = $isCorrect
            ? $this->battleService->calculateScore(
                true,
                (int) $serverTimeRemaining,
                (int) $duration,
                (int) $currentStreak
            )
            : 0;

        // ── Simpan jawaban ke Redis ──
        $this->battleService->recordAnswer(
            $room,
            $userId,
            $answer ?? 'none',
            $isCorrect,
            $scoreEarned
        );

        // ── Update skor & streak di Redis ──
        $this->battleService->updateScore(
            $room,
            $userId,
            $isCorrect,
            $scoreEarned
        );

        // ── Ambil skor terbaru untuk response ──
        $updatedScores = $this->battleService->getScores($room);
        $myScore       = $updatedScores[$userId] ?? null;

        return response()->json([
            'status'       => 'ok',
            'is_correct'   => $isCorrect,
            'score_earned' => $scoreEarned,
            'total_score'  => $myScore['total_score'] ?? 0,
            'rank'         => $myScore['rank'] ?? 0,
            'streak'       => $myScore['streak'] ?? 0,
        ]);
    }

    // ── Helper ───────────────────────────────

    private function doJoin(
        BattleRoom $room,
        int $userId,
        ?string $groupLabel
    ): BattleParticipant {
        $participant = BattleParticipant::create([
            'battle_room_id' => $room->id,
            'user_id'        => $userId,
            'group_label'    => $groupLabel,
            'joined_at'      => now(),
        ]);

        $participant->load('user');
        $this->battleService->addMember(
            $room, $participant
        );

        return $participant;
    }
}
