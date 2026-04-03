<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BattleRoom;
use App\Services\BattleService;
use Illuminate\Http\Request;

class ArenaController extends Controller
{
    public function __construct(
        protected BattleService $battleService
    ) {}

    public function index()
    {
        $rooms = BattleRoom::with('creator')
            ->latest()->paginate(10);
        return view('admin.gamification.arena.index',
            compact('rooms'));
    }

    public function create()
    {
        return view('admin.gamification.arena.create');
    }

    public function store(Request $request)
    {
        // Sprint 2
        return back()->with('info',
            'Sprint 2 belum diimplementasi');
    }

    public function destroy(BattleRoom $room)
    {
        $this->battleService->cleanup($room);
        $room->delete();
        return back()->with('success',
            'Room dihapus');
    }

    public function control(BattleRoom $room)
    {
        return view('admin.gamification.arena.control',
            compact('room'));
    }

    public function setState(
        Request $request, BattleRoom $room
    ) {
        // Sprint 3
        return response()->json(['status' => 'ok']);
    }

    public function controlData(BattleRoom $room)
    {
        $state = $this->battleService->getState($room);
        return response()->json($state);
    }

    public function display(BattleRoom $room)
    {
        return view('admin.gamification.arena.display',
            compact('room'));
    }

    public function displayData(BattleRoom $room)
    {
        $state = $this->battleService->getState($room);
        return response()->json($state);
    }

    public function podium(BattleRoom $room)
    {
        return view('admin.gamification.arena.podium',
            compact('room'));
    }

    public function debriefing(BattleRoom $room)
    {
        return view('admin.gamification.arena.debriefing',
            compact('room'));
    }
}
