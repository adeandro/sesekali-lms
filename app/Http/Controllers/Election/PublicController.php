<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use App\Models\ElectionPeriod;
use App\Models\Setting;
use App\Services\ElectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicController extends Controller
{
    public function __construct(private ElectionService $service) {}

    public function candidates(Request $request)
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        $period = ElectionPeriod::where('candidates_published', true)
            ->whereIn('status', ['ready', 'open', 'closed'])
            ->latest()
            ->first();

        if (! $period) {
            return view('election.public.empty', [
                'title'    => 'Profil Kandidat Paslon',
                'message'  => 'Belum ada informasi yang dipublikasikan.',
                'settings' => $settings,
            ]);
        }

        $candidates = $period->candidates()
            ->with(['chairman', 'vice'])
            ->orderBy('number')
            ->get();

        $isSlide = $request->query('tampilan') === 'slide';

        return view('election.public.candidates', compact('period', 'candidates', 'settings', 'isSlide'));
    }

    public function results(Request $request)
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        $period = ElectionPeriod::where('results_published', true)
            ->where('status', 'closed')
            ->latest()
            ->first();

        if (! $period) {
            return view('election.public.empty', [
                'title'    => 'Hasil Resmi Pemilihan',
                'message'  => 'Belum ada informasi yang dipublikasikan.',
                'settings' => $settings,
            ]);
        }

        $cacheKey = "pilketos_public_results_{$period->id}";
        $data = Cache::remember($cacheKey, 600, function () use ($period) {
            return [
                'tally'         => $this->service->tally($period),
                'participation' => $this->service->participation($period),
            ];
        });

        $tally         = $data['tally'];
        $participation = $data['participation'];

        return view('election.public.results', compact('period', 'tally', 'participation', 'settings'));
    }
}
