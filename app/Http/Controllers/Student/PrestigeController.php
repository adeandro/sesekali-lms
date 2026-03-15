<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\PrestigeService;
use Illuminate\Http\Request;

class PrestigeController extends Controller
{
    protected $prestigeService;

    public function __construct(PrestigeService $prestigeService)
    {
        $this->prestigeService = $prestigeService;
    }

    /**
     * Handle the prestige request from the student.
     */
    public function prestige(Request $request)
    {
        try {
            $user = auth()->user();
            $result = $this->prestigeService->doPrestige($user);

            return response()->json([
                'status'         => 'success',
                'message'        => 'Selamat! Kamu telah melakukan Prestige. XP kamu telah diriset dan kamu mendapatkan bonus APP.',
                'prestige_count' => $result['prestige_count'],
                'app_bonus'     => $result['app_bonus'],
                'badge_earned'   => $result['badge_earned'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
