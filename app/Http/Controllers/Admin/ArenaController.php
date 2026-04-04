<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BattleRoom;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Theme;
use App\Services\BattleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArenaController extends Controller
{
    public function __construct(
        protected BattleService $battleService
    ) {}

    // ── List Rooms ───────────────────────────

    public function index()
    {
        $rooms = BattleRoom::with('creator')
            ->latest()
            ->paginate(10);

        return view(
            'admin.gamification.arena.index',
            compact('rooms')
        );
    }

    // ── Create Room ──────────────────────────

    public function create()
    {
        $user = Auth::user();

        if ($user->role === 'superadmin') {
            $exams = Exam::with('subject')
                ->where('status', 'published')
                ->latest()
                ->get();
            $subjects = Subject::orderBy('name')->get();
        } else {
            $subjectIds = $user->subjects()
                ->pluck('subjects.id');
            $exams = Exam::with('subject')
                ->whereIn('subject_id', $subjectIds)
                ->where('status', 'published')
                ->latest()
                ->get();
            $subjects = Subject::whereIn('id', $subjectIds)
                ->orderBy('name')
                ->get();
        }

        $themes = Theme::where('is_active', true)
            ->orderBy('min_level')
            ->get(['id', 'name', 'slug',
                   'primary_color', 'min_level']);

        return view(
            'admin.gamification.arena.create',
            compact('exams', 'subjects', 'themes')
        );
    }

    // ── Store Room ───────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:100',
            'mode'                  => 'required|in:individual,group,class',
            'source_type'           => 'required|in:exam,manual',
            'source_id'             => 'required_if:source_type,exam|nullable|exists:exams,id',
            'question_ids'          => 'nullable|array',
            'question_ids.*'        => 'nullable|integer',
            'total_questions'       => 'required|integer|min:1|max:50',
            'duration_per_question' => 'required|integer|min:5|max:120',
            'group_count'           => 'required_if:mode,group|nullable|integer|min:2|max:8',
            'max_per_group'         => 'nullable|integer|min:1|max:20',
            'reward_rank1_exp'            => 'required|integer|min:0|max:9999',
            'reward_rank2_exp'            => 'required|integer|min:0|max:9999',
            'reward_rank3_exp'            => 'required|integer|min:0|max:9999',
            'reward_rank1_theme_id'       => 'nullable|exists:themes,id',
            'reward_rank2_theme_id'       => 'nullable|exists:themes,id',
            'reward_rank3_theme_id'       => 'nullable|exists:themes,id',
            'reward_participant_theme_id' => 'nullable|exists:themes,id',
            'reward_rank1_physical'       => 'nullable|string|max:255',
            'reward_rank2_physical'       => 'nullable|string|max:255',
            'reward_rank3_physical'       => 'nullable|string|max:255',
        ]);

        // Filter question_ids kosong sebelum validasi lanjut
        if (!empty($validated['question_ids'])) {
            $validated['question_ids'] = array_filter(
                $validated['question_ids'],
                fn($id) => !empty($id) && is_numeric($id)
            );
        }

        // Jika source_type=exam, abaikan question_ids sama sekali
        if ($validated['source_type'] === 'exam') {
            $validated['question_ids'] = [];
            
            // Ambil question_ids — HANYA multiple_choice
            $exam = Exam::with(['questions' => function ($q) {
                // Filter hanya soal PG, exclude essay
                $q->where('question_type', 'multiple_choice');
            }])->findOrFail($validated['source_id']);

            $allIds = $exam->questions->pluck('id')->toArray();

            if (empty($allIds)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'source_id' => 'Ujian ini tidak memiliki soal pilihan ganda (PG). Battle Arena hanya mendukung soal PG.'
                    ]);
            }

            $total = min(
                $validated['total_questions'],
                count($allIds)
            );
            $questionIds = collect($allIds)
                ->shuffle()
                ->take($total)
                ->values()
                ->toArray();

        } else {
            // Manual — validasi ulang semua harus PG
            $validIds = Question::whereIn('id',
                    $validated['question_ids'])
                ->where('question_type', 'multiple_choice')
                ->pluck('id')
                ->toArray();

            if (empty($validIds)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'question_ids' => 'Tidak ada soal PG yang dipilih.'
                    ]);
            }

            $questionIds = collect($validIds)
                ->shuffle()
                ->take($validated['total_questions'])
                ->values()
                ->toArray();
        }

        // Generate nama grup
        $groupNames  = null;
        $maxPerGroup = null;

        if ($validated['mode'] === 'group') {
            $allGroupNames = [
                'Merah','Biru','Hijau','Kuning',
                'Ungu','Oranye','Pink','Hitam'
            ];
            $groupNames = array_slice(
                $allGroupNames,
                0,
                (int) $validated['group_count']
            );
            $maxPerGroup = $validated['max_per_group']
                ?? (int) ceil(40 / $validated['group_count']);
        }

        $room = BattleRoom::create([
            'name'                  => $validated['name'],
            'mode'                  => $validated['mode'],
            'source_type'           => $validated['source_type'],
            'source_id'             => $validated['source_id'] ?? null,
            'question_ids'          => $questionIds,
            'total_questions'       => count($questionIds),
            'duration_per_question' => $validated['duration_per_question'],
            'group_count'           => $validated['group_count'] ?? null,
            'group_names'           => $groupNames,
            'max_per_group'         => $maxPerGroup,
            'status'                => 'waiting',
            'current_q_index'       => 0,
            'show_question_on_device'     => true,
            'reward_rank1_exp'            => $validated['reward_rank1_exp'],
            'reward_rank1_theme_id'       => $validated['reward_rank1_theme_id'] ?? null,
            'reward_rank2_exp'            => $validated['reward_rank2_exp'],
            'reward_rank2_theme_id'       => $validated['reward_rank2_theme_id'] ?? null,
            'reward_rank3_exp'            => $validated['reward_rank3_exp'],
            'reward_rank3_theme_id'       => $validated['reward_rank3_theme_id'] ?? null,
            'reward_participant_theme_id' => $validated['reward_participant_theme_id'] ?? null,
            'reward_rank1_physical'       => $validated['reward_rank1_physical'] ?? null,
            'reward_rank2_physical'       => $validated['reward_rank2_physical'] ?? null,
            'reward_rank3_physical'       => $validated['reward_rank3_physical'] ?? null,
            'created_by'            => Auth::id(),
        ]);

        return redirect()
            ->route('admin.gamification.arena.control',
                $room->token)
            ->with('success',
                'Room "' . $room->name
                . '" berhasil dibuat! Token: '
                . $room->token);
    }

    // ── Destroy ──────────────────────────────

    public function destroy(BattleRoom $room)
    {
        $this->battleService->cleanup($room);
        $room->delete();
        return back()->with('success', 'Room dihapus.');
    }

    // ── Control Panel ────────────────────────

    public function control(BattleRoom $room)
    {
        $state = $this->battleService->getState($room);
        return view(
            'admin.gamification.arena.control',
            compact('room', 'state')
        );
    }

    public function setState(
        Request $request,
        BattleRoom $room
    ) {
        $request->validate(['state' => 'required|string']);
        $newState = $request->input('state');
        $currentState = $this->battleService->getState($room)['state'];

        if ($newState === 'preview') {
            if ($currentState === 'lobby') {
                $room->update(['is_locked' => true]);
                $this->battleService->initScores($room);
                $this->cacheQuestionData($room, 0);
                $this->battleService->setState($room, 'preview', ['q_index' => 0]);
            }
        } elseif ($newState === 'question') {
            $this->battleService->setState($room, 'question');
        } elseif ($newState === 'discussion') {
            $this->battleService->setState($room, 'discussion');
        } elseif ($newState === 'leaderboard') {
            $this->battleService->setState($room, 'leaderboard');
        } elseif ($newState === 'next') {
            $this->accumulateAnswers($room);
            $res = $this->battleService->nextQuestion($room);
            if ($res['state'] === 'preview') {
                $this->cacheQuestionData($room, $res['q_index']);
            } elseif ($res['state'] === 'finish') {
                $this->processFinish($room);
            }
        } elseif ($newState === 'finish') {
            if ($currentState !== 'finish') {
                $this->accumulateAnswers($room);
                $this->battleService->setState($room, 'finish');
                $this->processFinish($room);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function accumulateAnswers(BattleRoom $room)
    {
        $state = $this->battleService->getState($room);
        $qIndex = $state['q_index'] ?? 0;
        $questionId = $room->question_ids[$qIndex] ?? null;
        
        $currentAnswers = $this->battleService->getAnswers($room);
        $members = $this->battleService->getMembers($room);
        
        $accumulatedKey = $room->cacheKey('all_answers');
        $allAnswers = \Illuminate\Support\Facades\Cache::get($accumulatedKey, []);
        
        foreach ($currentAnswers as $userId => $ans) {
            $memberId = $members[$userId]['id'] ?? null;
            if ($memberId) {
                $allAnswers[] = [
                    'battle_room_id' => $room->id,
                    'battle_participant_id' => $memberId,
                    'question_id' => $questionId,
                    'q_index' => $qIndex,
                    'chosen_option' => $ans['answer'],
                    'is_correct' => $ans['is_correct'] ? 1 : 0,
                    'score_earned' => $ans['score_earned'],
                    'answered_at' => date('Y-m-d H:i:s', $ans['answered_at']),
                ];
            }
        }
        
        \Illuminate\Support\Facades\Cache::put($accumulatedKey, $allAnswers, BattleService::TTL);
    }

    private function cacheQuestionData(BattleRoom $room, int $qIndex)
    {
        $key = "battle:{$room->token}:q:{$qIndex}";
        
        return \Illuminate\Support\Facades\Cache::remember($key, BattleService::TTL, function () use ($room, $qIndex) {
            $questionId = $room->question_ids[$qIndex] ?? null;
            if (!$questionId) return null;
            
            $q = \App\Models\Question::find($questionId);
            if (!$q) return null;
            
            return [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'question_image' => $q->question_image ? asset('storage/'.$q->question_image) : null,
                'options' => [
                    'a' => ['text' => $q->option_a, 'image' => $q->option_a_image ? asset('storage/'.$q->option_a_image) : null],
                    'b' => ['text' => $q->option_b, 'image' => $q->option_b_image ? asset('storage/'.$q->option_b_image) : null],
                    'c' => ['text' => $q->option_c, 'image' => $q->option_c_image ? asset('storage/'.$q->option_c_image) : null],
                    'd' => ['text' => $q->option_d, 'image' => $q->option_d_image ? asset('storage/'.$q->option_d_image) : null],
                    'e' => ['text' => $q->option_e, 'image' => $q->option_e_image ? asset('storage/'.$q->option_e_image) : null],
                ],
                'correct_answer' => $q->correct_answer,
                'explanation' => $q->explanation,
                'duration' => $room->duration_per_question,
            ];
        });
    }

    private function processFinish(BattleRoom $room)
    {
        $room->update([
            'status' => 'finished',
            'ended_at' => now(),
        ]);
        
        $scores = $this->battleService->getScores($room);
        $members = $this->battleService->getMembers($room);
        
        foreach ($scores as $userId => $score) {
            if (isset($members[$userId])) {
                \App\Models\BattleParticipant::where('id', $members[$userId]['id'])->update([
                    'total_score' => $score['total_score'],
                    'correct_count' => $score['correct'],
                    'wrong_count' => $score['wrong'],
                    'rank' => $score['rank'],
                    'finished_at' => now(),
                ]);
            }
        }
        
        $allAnswers = \Illuminate\Support\Facades\Cache::get($room->cacheKey('all_answers'), []);
        if (!empty($allAnswers)) {
            \App\Models\BattleAnswer::insert($allAnswers);
        }
        
        \Illuminate\Support\Facades\Cache::forget($room->cacheKey('all_answers'));

        // ── BARU: Distribusi Reward ───────────────
        $this->distributeRewards($room, $scores, $members);

        // NOTE: Jangan panggil battleService->cleanup($room) di sini.
        // Data Redis (state, scores, members) harus tetap ada agar proyektor
        // bisa menampilkan animasi podium dan pemenang.
        // Cache akan expire otomatis sesuai TTL (4 Jam).
    }

    private function distributeRewards(
        BattleRoom $room,
        array $scores,
        array $members
    ): void {
        if (empty($scores)) return;

        $achievementService = app(
            \App\Services\AchievementService::class
        );

        // Map reward EXP & tema per rank
        $rankRewards = [
            1 => [
                'exp'      => $room->reward_rank1_exp,
                'theme_id' => $room->reward_rank1_theme_id,
                'physical' => $room->reward_rank1_physical,
            ],
            2 => [
                'exp'      => $room->reward_rank2_exp,
                'theme_id' => $room->reward_rank2_theme_id,
                'physical' => $room->reward_rank2_physical,
            ],
            3 => [
                'exp'      => $room->reward_rank3_exp,
                'theme_id' => $room->reward_rank3_theme_id,
                'physical' => $room->reward_rank3_physical,
            ],
        ];

        $themeExpiresAt = now()->addDays(30);

        // ─── PERSIAPAN MODE GRUP ───
        $winningGroup = null;
        if ($room->mode === 'group') {
            $groupScores = $this->battleService->getGroupScores($room);
            $winningGroup = collect($groupScores)->sortByDesc('total_score')->first()['group_label'] ?? null;
        }

        foreach ($scores as $userId => $score) {
            $rank = $score['rank'] ?? 0;
            $groupLabel = $score['group_label'] ?? null;

            if ($room->mode === 'group' && $groupLabel) {
                $rank = ($groupLabel === $winningGroup) ? 1 : 999;
            }

            if (!$rank) continue;

            $user = \App\Models\User::find($userId);
            if (!$user) continue;

            $expToAdd = isset($rankRewards[$rank]) ? ($rankRewards[$rank]['exp'] ?? 0) : 50;
            if ($expToAdd > 0) {
                $achievementService->awardXp($user, $expToAdd);
            }

            $themeId = null;
            if (isset($rankRewards[$rank])) {
                $themeId = $rankRewards[$rank]['theme_id'];
            } elseif ($room->reward_participant_theme_id) {
                $alreadyHas = \Illuminate\Support\Facades\DB::table('theme_user')
                    ->where('user_id', $userId)
                    ->where('theme_id', $room->reward_participant_theme_id)
                    ->exists();
                if (!$alreadyHas) {
                    $themeId = $room->reward_participant_theme_id;
                }
            }

            if ($themeId) {
                $existing = \Illuminate\Support\Facades\DB::table('theme_user')
                    ->where('user_id', $userId)
                    ->where('theme_id', $themeId)
                    ->first();

                if ($existing) {
                    \Illuminate\Support\Facades\DB::table('theme_user')
                        ->where('id', $existing->id)
                        ->update([
                            'expires_at' => $themeExpiresAt,
                            'updated_at' => now(),
                        ]);
                } else {
                    \Illuminate\Support\Facades\DB::table('theme_user')->insert([
                        'user_id'    => $userId,
                        'theme_id'   => $themeId,
                        'expires_at' => $themeExpiresAt,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if (isset($rankRewards[$rank]['physical']) && $rankRewards[$rank]['physical']) {
                $participantId = $members[$userId]['id'] ?? null;
                if ($participantId) {
                    \App\Models\BattleParticipant::where('id', $participantId)->update([
                        'physical_reward' => $rankRewards[$rank]['physical']
                    ]);
                }
            }
        }
    }

    public function toggleLock(BattleRoom $room)
    {
        $room->update([
            'is_locked' => !$room->is_locked,
        ]);

        return response()->json([
            'status'    => 'ok',
            'is_locked' => $room->is_locked,
        ]);
    }

    public function toggleShowQuestion(
        Request $request,
        BattleRoom $room
    ) {
        $room->update([
            'show_question_on_device' =>
                !$room->show_question_on_device,
        ]);

        return response()->json([
            'status'              => 'ok',
            'show_question_on_device' =>
                $room->show_question_on_device,
        ]);
    }

    public function controlData(BattleRoom $room)
    {
        $state   = $this->battleService->getState($room);
        $members = $this->battleService->getMembers($room);
        $scores  = $this->battleService->getScores($room);
        $answersCount = count($this->battleService->getAnswers($room));
        
        $qIndex = $state['q_index'] ?? 0;
        $question = \Illuminate\Support\Facades\Cache::get("battle:{$room->token}:q:{$qIndex}");

        return response()->json([
            'state'   => $state,
            'members' => array_values($members),
            'scores'  => array_values($scores),
            'count'   => count($members),
            'answers_count' => $answersCount,
            'question' => $question,
            'show_question_on_device' => $room->show_question_on_device,
            'is_locked' => $room->is_locked,
        ]);
    }

    // ── Display (Proyektor) ──────────────────

    public function display(BattleRoom $room)
    {
        return view(
            'admin.gamification.arena.display',
            compact('room')
        );
    }

    public function displayData(BattleRoom $room)
    {
        $state   = $this->battleService->getState($room);
        $state['mode'] = $room->mode; // Force sync
        
        $members = $this->battleService->getMembers($room);
        $scores  = $this->battleService->getScores($room);
        
        $qIndex = $state['q_index'] ?? 0;
        $question = \Illuminate\Support\Facades\Cache::get("battle:{$room->token}:q:{$qIndex}");
        
        // Hide correct logic if not in discussion
        if ($question && $state['state'] !== 'discussion') {
            unset($question['correct_answer']);
            unset($question['explanation']);
        }
        
        $stats = [];
        if ($state['state'] === 'discussion') {
            $stats = $this->battleService->getAnswerStats($room, ['a', 'b', 'c', 'd', 'e']);
        }

        $groupScores = [];
        if ($room->mode === 'group') {
            $groupScores = $this->battleService->getGroupScores($room);
        }

        return response()->json([
            'state'   => $state,
            'members' => array_values($members),
            'scores'  => array_values($scores),
            'group_scores' => $groupScores,
            'question' => $question,
            'stats' => $stats,
            'is_locked' => $room->is_locked,
        ]);
    }

    // ── Podium & Debriefing ──────────────────

    public function podium(BattleRoom $room)
    {
        return view(
            'admin.gamification.arena.podium',
            compact('room')
        );
    }

    public function debriefing(BattleRoom $room)
    {
        $room->load('creator');

        $participants = \App\Models\BattleParticipant::where(
            'battle_room_id', $room->id
        )->with('user')
         ->orderBy('rank')
         ->get();

        // Group scores jika mode group/class
        $groupScores = null;
        if (in_array($room->mode, ['group', 'class'])) {
            $groupScores = $participants
                ->groupBy('group_label')
                ->map(fn($group) => [
                    'label'       => $group->first()->group_label,
                    'total_score' => $group->sum('total_score'),
                    'members'     => $group->count(),
                    'avg_score'   => round(
                        $group->avg('total_score')
                    ),
                    'correct'     => $group->sum('correct_count'),
                ])
                ->sortByDesc('total_score')
                ->values();
        }

        return view(
            'admin.gamification.arena.debriefing',
            compact('room', 'participants', 'groupScores')
        );
    }

    // ── AJAX: Preview soal dari exam ─────────

    public function examPreview(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
        ]);

        $exam = Exam::with(['questions', 'subject'])
            ->findOrFail($request->exam_id);

        // Gunakan kolom question_type (bukan 'type')
        $pgCount = $exam->questions
            ->where('question_type', 'multiple_choice')
            ->count();

        $essayCount = $exam->questions
            ->where('question_type', 'essay')
            ->count();

        $note = null;
        if ($essayCount > 0 && $pgCount === 0) {
            $note = 'Ujian ini hanya berisi soal essay dan tidak bisa digunakan di Battle Arena.';
        } elseif ($essayCount > 0) {
            $note = $essayCount . ' soal essay akan diabaikan. Hanya ' . $pgCount . ' soal PG yang digunakan.';
        }

        return response()->json([
            'total'       => $exam->questions->count(),
            'pg_count'    => $pgCount,
            'essay_count' => $essayCount,
            'subject'     => $exam->subject->name ?? '-',
            'usable'      => $pgCount > 0,
            'note'        => $note,
        ]);
    }
}
