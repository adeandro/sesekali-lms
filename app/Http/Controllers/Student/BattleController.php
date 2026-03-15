<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BattleRoom;
use App\Models\BattleParticipant;
use App\Models\Question;
use App\Services\BattleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BattleController extends Controller
{
    protected $battleService;

    public function __construct(BattleService $battleService)
    {
        $this->battleService = $battleService;
    }

    /**
     * Submit an answer in the live battle arena.
     */
    public function answer(Request $request)
    {
        $request->validate([
            'battle_room_id' => 'required|exists:battle_rooms,id',
            'participant_id' => 'required|exists:battle_participants,id',
            'answer'         => 'required|string',
        ]);

        $room = BattleRoom::findOrFail($request->battle_room_id);
        $participant = BattleParticipant::findOrFail($request->participant_id);

        // Security check
        abort_if($participant->user_id !== Auth::id(), 403);
        abort_if($room->status === 'finished', 422, 'Battle sudah selesai.');
        
        if ($participant->status !== 'active') {
            return response()->json(['error' => 'Anda sudah gugur atau tidak aktif.'], 422);
        }

        // Determine correctness
        $questionIds = $room->question_ids ?? [];
        $idx = $participant->current_question_index;
        $questionId = $questionIds[$idx] ?? null;

        if (!$questionId) {
            return response()->json(['error' => 'Semua soal sudah terjawab.'], 422);
        }

        $question = Question::findOrFail($questionId);
        $isCorrect = strtolower($request->answer) === strtolower($question->correct_answer);

        // Process via BattleService
        $result = $this->battleService->handleAnswer($participant, $isCorrect);

        // Update question index
        $participant->increment('current_question_index');

        return response()->json([
            'is_correct'    => $isCorrect,
            'hp'            => $result['hp'],
            'correct_count' => $result['correct_count'],
            'streak'        => $result['streak'],
            'multiplier'    => $result['multiplier'],
            'exp_earned'    => $result['exp_earned'],
            'status'        => $result['status'],
        ]);
    }

    /**
     * Activate a power-up card during the battle.
     */
    public function activatePowerup(Request $request, \App\Services\PowerupService $powerupService)
    {
        $request->validate([
            'powerup_card_id' => 'required|exists:powerup_cards,id',
            'battle_room_id'  => 'required|exists:battle_rooms,id',
        ]);

        $card = \App\Models\PowerupCard::findOrFail($request->powerup_card_id);
        $participant = BattleParticipant::where('battle_room_id', $request->battle_room_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            $result = $powerupService->activate($card, $participant);

            return response()->json([
                'success'           => true,
                'type'              => $result['type'],
                'effect'            => $result['effect'],
                'participant_state' => [
                    'hp'                 => $participant->hp,
                    'correct_count'      => $participant->correct_count,
                    'active_powerup'     => $participant->active_powerup,
                    'powerup_used_count' => $participant->powerup_used_count,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
