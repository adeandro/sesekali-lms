<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BattleRoom;
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
        // Sprint 2
        return back()->with('info',
            'Sprint 2 belum diimplementasi');
    }

    public function lobby(BattleRoom $room)
    {
        return view('student.arena.lobby',
            compact('room'));
    }

    public function lobbyStatus(BattleRoom $room)
    {
        $state = $this->battleService->getState($room);
        $participant = $room->participants()
            ->where('user_id', Auth::id())
            ->first();

        return response()->json([
            'state'          => $state['state'],
            'participant_id' => $participant?->id,
        ]);
    }

    public function battle(BattleRoom $room)
    {
        return view('student.arena.battle',
            compact('room'));
    }

    public function battleData(BattleRoom $room)
    {
        $state = $this->battleService->getState($room);
        return response()->json($state);
    }

    public function submitAnswer(
        Request $request, BattleRoom $room
    ) {
        // Sprint 4
        return response()->json(['status' => 'ok']);
    }
}
