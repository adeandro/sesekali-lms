<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BattleRoom;
use App\Models\BattleParticipant;
use App\Models\BattleAnswer;
use App\Models\Exam;
use App\Models\Question;
use App\Models\User;
use App\Models\RewardCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notifications\GamificationNotification;

class ArenaController extends Controller
{
    protected $battleService;

    public function __construct(\App\Services\BattleService $battleService)
    {
        $this->battleService = $battleService;
    }

    // ── Admin: Room Index ─────────────────────────────────────────────────

    public function index()
    {
        $rooms = BattleRoom::with('creator')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.gamification.arena.index', compact('rooms'));
    }

    // ── Admin: Create Room Form ───────────────────────────────────────────

    public function create()
    {
        $exams = Exam::where('status', 'published')->orderBy('title')->get();
        $themes = \App\Models\Theme::where('is_active', true)->orderBy('id')->get();
        return view('admin.gamification.arena.create', compact('exams', 'themes'));
    }

    // ── Admin: Store Room ─────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'mode'             => 'required|in:individual,group,class',
            'source_type'      => 'required|in:exam,question_bank',
            'source_id'        => 'nullable|exists:exams,id',
            'winner_count'     => 'required|integer|min:1|max:10',
            'duration_minutes' => 'required|integer|min:5|max:180',
            'penalty_hp'       => 'required|integer|min:5|max:50',
            'lock_on_start'    => 'boolean',
            'rewards'          => 'required|array',
            'rewards.*.exp'    => 'required|integer|min:0',
            'rewards.*.theme'  => 'nullable|string',
            'physical_reward.enabled'     => 'nullable|boolean',
            'physical_reward.description' => 'required_if:physical_reward.enabled,1|nullable|string|max:150',
            'physical_reward.eligibility' => 'required_if:physical_reward.enabled,1|nullable|in:rank_1,top_3',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $settings = ['rewards' => $validated['rewards']];
            
            // Map physical reward cleanly if enabled
            if (!empty($validated['physical_reward']['enabled'])) {
                $settings['physical_reward'] = [
                    'description' => $validated['physical_reward']['description'],
                    'eligibility' => $validated['physical_reward']['eligibility'],
                ];
            }

            $room = BattleRoom::create([
                'name'             => $validated['name'],
                'mode'             => $validated['mode'],
                'source_type'      => $validated['source_type'],
                'source_id'        => $validated['source_id'],
                'winner_count'     => $validated['winner_count'],
                'duration_minutes' => $validated['duration_minutes'],
                'penalty_hp'       => $validated['penalty_hp'],
                'created_by'    => Auth::id(),
                'lock_on_start' => $request->boolean('lock_on_start', true),
                'settings'      => $settings,
            ]);

            // Pre-load questions from exam
            if ($room->source_type === 'exam' && $room->source_id) {
                $exam = Exam::with('questions')->find($room->source_id);
                if ($exam) {
                    $ids = $exam->questions->pluck('id')->toArray();
                    $room->update([
                        'question_ids'    => $ids,
                        'total_questions' => count($ids),
                    ]);
                }
            }
        });

        $room = BattleRoom::where('created_by', Auth::id())->latest()->first();
        return redirect()->route('admin.gamification.arena.lobby', $room)
            ->with('success', 'Battle Room "' . $room->name . '" berhasil dibuat!');
    }

    // ── Admin: Lobby View ─────────────────────────────────────────────────

    public function lobby(BattleRoom $room)
    {
        $room->load(['participants.user']);

        // Group participants by class for Fleet display
        $fleetGroups = $room->participants->groupBy('class_id');

        return view('admin.gamification.arena.lobby', compact('room', 'fleetGroups'));
    }

    // ── Admin: Ignite (Start Battle) ──────────────────────────────────────

    public function ignite(BattleRoom $room)
    {
        if ($room->status !== 'waiting') {
            return back()->with('error', 'Battle sudah dimulai atau selesai.');
        }

        if ($room->participants()->count() === 0) {
            return back()->with('error', 'Belum ada peserta yang bergabung.');
        }

        $room->update([
            'status'     => 'ongoing',
            'started_at' => now(),
        ]);

        // Invalidate cache status room
        // agar semua siswa langsung dapat status terbaru
        \Cache::forget('battle_room_status_' . $room->id);

        return redirect()->route('admin.gamification.arena.spectator', $room)
            ->with('success', 'Battle dimulai! 🔥');
    }

    // ── Admin: Spectator (Live Track) ─────────────────────────────────────

    public function spectator(BattleRoom $room)
    {
        $room->load(['participants.user']);
        return view('admin.gamification.arena.spectator', compact('room'));
    }

    // ── Admin: Spectator Poll (AJAX – called by wire:poll) ────────────────

    public function spectatorData(BattleRoom $room)
    {
        $room->load(['participants.user']);

        $data = [
            'status'            => $room->status,
            'remaining_seconds' => $room->remainingSeconds(),
            'is_sudden_death'   => $room->isSuddenDeath(),
            'fleet'             => array_values($room->fleetProgress()),
            'participants'      => $room->participants->map(fn ($p) => [
                'id'            => $p->id,
                'name'          => $p->user->name,
                'class_id'      => $p->class_id,
                'hp'            => $p->hp,
                'correct'       => $p->correct_count,
                'status'        => $p->status,
                'progress'      => $p->progressPercent($room->total_questions),
                'avatar_url'    => $p->user->photo_url,
            ]),
        ];

        // Auto-finish check
        if ($room->status === 'ongoing' || $room->status === 'sudden_death') {
            $this->battleService->checkSuddenDeath($room);
            $this->checkAutoFinish($room);
            $room->refresh();
            $data['status'] = $room->status;
        }

        return response()->json($data);
    }

    // ── Admin: Force Finish ───────────────────────────────────────────────

    public function finish(BattleRoom $room)
    {
        $this->finalizeRoom($room);
        return redirect()->route('admin.gamification.arena.podium', $room);
    }

    // ── Admin: Podium ─────────────────────────────────────────────────────

    public function podium(BattleRoom $room)
    {
        $room->load(['participants.user']);

        if ($room->mode === 'class') {
            // Fleet podium — ranked by fleet progress
            $fleet   = collect($room->fleetProgress())->values();
            $winners = $fleet->take(3);
            return view('admin.gamification.arena.podium', compact('room', 'winners'));
        }

        // Individual / Group podium
        $winners = $room->participants()
            ->with('user')
            ->whereNotNull('rank')
            ->orderBy('rank')
            ->take(3)
            ->get();

        return view('admin.gamification.arena.podium', compact('room', 'winners'));
    }

    // ── Admin: Debriefing ─────────────────────────────────────────────────

    public function debriefing(BattleRoom $room)
    {
        // Questions with most wrong answers
        $toughest = BattleAnswer::with('question')
            ->where('is_correct', false)
            ->whereIn('battle_participant_id', $room->participants()->pluck('id'))
            ->selectRaw('question_id, COUNT(*) as wrong_count')
            ->groupBy('question_id')
            ->orderByDesc('wrong_count')
            ->take(10)
            ->get();

        return view('admin.gamification.arena.debriefing', compact('room', 'toughest'));
    }

    // ── Admin: Delete Room ────────────────────────────────────────────────

    public function destroy(BattleRoom $room)
    {
        $room->delete();
        return redirect()->route('admin.gamification.arena.index')
            ->with('success', 'Battle Room dihapus.');
    }

    // ── Student: Join Lobby ───────────────────────────────────────────────

    public function studentJoin(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);
        
        $room = BattleRoom::where('code', strtoupper($request->code))
            ->whereIn('status', ['waiting'])
            ->first();

        if (!$room) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak ditemukan atau sudah dimulai.'
                ], 422);
            }
            return back()->withErrors(['code' => 'Token tidak ditemukan atau sudah dimulai.'])
                         ->withInput()
                         ->with('open_arena_modal', true);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect' => route('student.arena.lobby', $room)
            ]);
        }

        return redirect()->route('student.arena.lobby', $room);
    }

    public function studentLobby(BattleRoom $room)
    {
        $user = Auth::user();

        // Register participant if not already there
        $participant = BattleParticipant::firstOrCreate(
            ['battle_room_id' => $room->id, 'user_id' => $user->id],
            [
                'class_id'    => $user->grade . ($user->class_group ? '-' . $user->class_group : ''),
                'hp'          => 100,
                'status'      => 'active',
                'last_seen_at' => now(),
            ]
        );

        // Reset participant cache untuk user ini
        \Cache::forget(
            'participant_id_' . Auth::id() . '_' . $room->id
        );

        // Redirect to battle if already started
        if (in_array($room->status, ['ongoing'])) {
            return redirect()->route('student.arena.battle', [$room, $participant]);
        }

        return view('student.arena.lobby', compact('room', 'participant'));
    }

    // ── Student: Lobby Status Poll ────────────────────────────────────────

    public function studentLobbyStatus(BattleRoom $room)
    {
        // Ambil status room dari cache — hindari DB query
        // Cache di-invalidate saat ignite/finish
        $cacheKey = 'battle_room_status_' . $room->id;

        $status = \Cache::remember($cacheKey, 30, function () use ($room) {
            return \DB::table('battle_rooms')
                ->where('id', $room->id)
                ->value('status');
        });

        // Update last_seen_at hanya jika sudah
        // lebih dari 10 detik sejak update terakhir
        // — kurangi write ke DB saat banyak siswa
        $seenKey = 'participant_seen_' . Auth::id() . '_' . $room->id;

        if (!\Cache::has($seenKey)) {
            \DB::table('battle_participants')
                ->where('battle_room_id', $room->id)
                ->where('user_id', Auth::id())
                ->update(['last_seen_at' => now()]);

            // Throttle update last_seen_at — max 1x per 10 detik
            \Cache::put($seenKey, 1, 10);
        }

        // Ambil participant_id dari cache per user
        $participantKey = 'participant_id_'
            . Auth::id() . '_' . $room->id;

        $participantId = \Cache::remember(
            $participantKey, 300,
            function () use ($room) {
                return \DB::table('battle_participants')
                    ->where('battle_room_id', $room->id)
                    ->where('user_id', Auth::id())
                    ->value('id');
            }
        );

        return response()->json([
            'status'         => $status,
            'participant_id' => $participantId,
        ]);
    }

    // ── Student: Battle View ──────────────────────────────────────────────

    public function battle(BattleRoom $room, BattleParticipant $participant)
    {
        // Security: participant must belong to auth user
        abort_if($participant->user_id != Auth::id(), 403);
        if ($room->status === 'waiting') {
            return redirect()->route('student.arena.lobby', $room);
        }

        if (in_array($room->status, ['finished']) || $participant->status === 'disqualified') {
            return view('student.arena.finished', compact('room', 'participant'));
        }

        // Fetch current question
        $questionIds = $room->question_ids ?? [];
        $idx         = $participant->current_question_index;
        $questionId  = $questionIds[$idx] ?? null;
        $question    = $questionId ? Question::find($questionId) : null;

        if (!$question) {
            // All questions answered
            $participant->update(['status' => 'finished', 'finished_at' => now()]);
            $this->checkAutoFinish($room);
            return view('student.arena.finished', compact('room', 'participant'));
        }

        // Shuffle options if needed (randomize_options from source exam)
        $options = [
            'a' => $question->option_a,
            'b' => $question->option_b,
            'c' => $question->option_c,
            'd' => $question->option_d,
            'e' => $question->option_e,
        ];
        $options = array_filter($options);

        return view('student.arena.battle', compact('room', 'participant', 'question', 'options'));
    }

    // ── Student: Submit Answer (AJAX) ─────────────────────────────────────

    public function submitAnswer(Request $request, BattleRoom $room, BattleParticipant $participant)
    {
        abort_if($participant->user_id != Auth::id(), 403);

        if ($room->status !== 'ongoing' || $participant->status !== 'active') {
            return response()->json(['error' => 'Battle tidak aktif atau Anda sudah gugur.'], 422);
        }

        $request->validate(['answer' => 'required|in:a,b,c,d,e']);

        $questionIds = $room->question_ids ?? [];
        $idx         = $participant->current_question_index;
        $questionId  = $questionIds[$idx] ?? null;

        if (!$questionId) {
            return response()->json(['error' => 'Soal tidak ditemukan.'], 422);
        }

        $question  = Question::findOrFail($questionId);
        $isCorrect = strtolower($request->answer) === strtolower($question->correct_answer);

        // Determine HP delta
        $penalty  = $room->penalty_hp;
        if ($room->isSuddenDeath()) $penalty *= 2; // Sudden Death!
        $hpDelta  = $isCorrect ? 0 : -$penalty;

        DB::transaction(function () use ($participant, $questionId, $request, $isCorrect, $hpDelta) {
            BattleAnswer::create([
                'battle_participant_id' => $participant->id,
                'question_id'          => $questionId,
                'chosen_option'        => $request->answer,
                'is_correct'           => $isCorrect,
                'hp_delta'             => $hpDelta,
                'answered_at'          => now(),
            ]);

            // Update participant stats
            $updates = ['current_question_index' => $participant->current_question_index + 1];
            if ($isCorrect) $updates['correct_count'] = $participant->correct_count + 1;
            else $updates['wrong_count'] = $participant->wrong_count + 1;

            $participant->update($updates);
            $participant->refresh();

            // Apply HP damage
            if ($hpDelta < 0) {
                $participant->applyHpDelta($hpDelta);
            }
        });

        $participant->refresh();
        $room->refresh();

        // Check if participant finished all questions
        if ($participant->current_question_index >= count($room->question_ids ?? [])) {
            $participant->update(['status' => 'finished', 'finished_at' => now()]);
            $this->checkAutoFinish($room);
        }

        // Award EXP for correct answer
        if ($isCorrect) {
            $participant->user->increment('total_exp', 5);
        }

        return response()->json([
            'is_correct'  => $isCorrect,
            'hp'          => $participant->hp,
            'status'      => $participant->status,
            'next_index'  => $participant->current_question_index,
            'sudden_death'=> $room->isSuddenDeath(),
        ]);
    }

    // ── Student: Tab Focus Penalty ────────────────────────────────────────

    public function tabPenalty(BattleRoom $room, BattleParticipant $participant)
    {
        abort_if($participant->user_id != Auth::id(), 403);

        if ($room->status !== 'ongoing' || $participant->status !== 'active') {
            return response()->json(['ok' => false]);
        }

        DB::transaction(function () use ($participant) {
            $participant->applyHpDelta(-10);
        });

        $participant->refresh();

        return response()->json([
            'hp'     => $participant->hp,
            'status' => $participant->status,
        ]);
    }

    // ── Student: Heartbeat ────────────────────────────────────────────────

    public function heartbeat(BattleRoom $room, BattleParticipant $participant)
    {
        abort_if($participant->user_id != Auth::id(), 403);
        $participant->update(['last_seen_at' => now()]);

        return response()->json([
            'status'          => $room->status,
            'hp'              => $participant->hp,
            'participant_status' => $participant->status,
            'remaining'       => $room->remainingSeconds(),
            'sudden_death'    => $room->isSuddenDeath(),
        ]);
    }

    // ── Internal: Auto-finish Logic ───────────────────────────────────────

    protected function checkAutoFinish(BattleRoom $room): void
    {
        $room->refresh();
        if ($room->status !== 'ongoing') return;

        $active = $room->participants()->where('status', 'active')->count();
        $timeUp = $room->remainingSeconds() <= 0;
        $allDone = $room->participants()->whereIn('status', ['active'])->count() === 0;

        if ($timeUp || $allDone) {
            $this->finalizeRoom($room);
        }
    }

    protected function finalizeRoom(BattleRoom $room): void
    {
        if ($room->status === 'finished') return;

        DB::transaction(function () use ($room) {
            $room->update(['status' => 'finished', 'ended_at' => now()]);

            // Invalidate cache status room
            \Cache::forget('battle_room_status_' . $room->id);

            if ($room->mode === 'class') {
                // Rank fleets
                $fleet = $room->fleetProgress();
                $rank  = 1;
                foreach ($fleet as $classId => $data) {
                    // Award rank to all members of this fleet
                    $room->participants()
                        ->where('class_id', $classId)
                        ->update(['rank' => $rank]);
                    $rank++;
                }
            } else {
                // Individual / Group: rank by correct_count desc then wrong_count asc
                $participants = $room->participants()
                    ->orderByDesc('correct_count')
                    ->orderBy('wrong_count')
                    ->orderBy('finished_at')
                    ->get();

                $rank = 1;
                foreach ($participants as $p) {
                    $p->update(['rank' => $rank++]);
                }
            }

            // Grant EXP and Theme Rewards
            $participants = $room->participants()->with('user')->get();
            $rewards = $room->settings['rewards'] ?? [];
            
            foreach ($participants as $p) {
                // Determine Reward based on Rank
                if ($p->rank === 1) {
                    $config = $rewards['rank_1'] ?? ['exp' => 500, 'theme' => 'champion'];
                } elseif ($p->rank === 2) {
                    $config = $rewards['rank_2'] ?? ['exp' => 300, 'theme' => 'elite-silver'];
                } elseif ($p->rank === 3) {
                    $config = $rewards['rank_3'] ?? ['exp' => 200, 'theme' => 'master-bronze'];
                } else {
                    $config = $rewards['participant'] ?? ['exp' => 100, 'theme' => 'survivor-common'];
                }

                $expBonus = (int) ($config['exp'] ?? 0);
                $theme = $config['theme'] ?? null;

                // Apply Rewards
                if ($expBonus > 0) {
                    app(\App\Services\AchievementService::class)->awardXp($p->user, $expBonus);
                }

                // Check for achievements (including the new arena_win_count)
                app(\App\Services\AchievementService::class)->checkAchievements($p->user);
                if ($theme && $p->user->ui_theme !== $theme) {
                    $p->user->update(['ui_theme' => $theme]);
                    $p->user->notify(new GamificationNotification(
                        'Tema Baru Terbuka!',
                        "Kamu mendapatkan tema eksklusif: {$theme} dari Battle Arena!",
                        'fa-palette',
                        'bg-purple-100 text-purple-600'
                    ));
                }

                // Handle Physical Reward Generation
                $physical = $room->settings['physical_reward'] ?? null;
                if ($physical) {
                    $isEligible = false;
                    if ($physical['eligibility'] === 'rank_1' && $p->rank === 1) {
                        $isEligible = true;
                    } elseif ($physical['eligibility'] === 'top_3' && $p->rank <= 3) {
                        $isEligible = true;
                    }

                    if ($isEligible) {
                        $coupon = RewardCoupon::create([
                            'user_id'        => $p->user_id,
                            'battle_room_id' => $room->id,
                            'description'    => $physical['description'],
                            'code'           => Str::upper(Str::random(10)),
                            'status'         => 'active',
                        ]);

                        $p->user->notify(new GamificationNotification(
                            'Kupon Hadiah Fisik!',
                            "Kamu memenangkan: {$physical['description']}. Segera klaim di menu Kupon Fisik!",
                            'fa-ticket-alt',
                            'bg-amber-100 text-amber-600'
                        ));
                    }
                }
            }
        });
    }
}
