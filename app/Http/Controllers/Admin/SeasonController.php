<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SeasonController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────

    public function index()
    {
        $seasons = Season::orderByDesc('started_at')->paginate(10);
        return view('admin.gamification.seasons.index', compact('seasons'));
    }

    // ── Create / Store ────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.gamification.seasons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'semester_type' => 'required|in:ganjil,genap',
            'academic_year' => 'required|regex:/^\d{4}\/\d{4}$/',
            'started_at'    => 'required|date',
        ]);

        $validated['status'] = 'active';

        $seasonService = app(\App\Services\SeasonService::class);
        $season = $seasonService->startNewSeason($validated, auth()->user());

        return redirect()->route('admin.gamification.seasons.index')
            ->with('success', "Season [{$season->name}] berhasil dibuat!");
    }

    // ── Edit / Update ─────────────────────────────────────────────────────

    public function edit(Season $season)
    {
        return view('admin.gamification.seasons.edit', compact('season'));
    }

    public function update(Request $request, Season $season)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'semester_type' => 'required|in:ganjil,genap',
            'academic_year' => 'required|regex:/^\d{4}\/\d{4}$/',
            'started_at'    => 'required|date',
        ]);

        // No special status handling here for simplicity, or we could handle activation

        $season->update($validated);

        return redirect()->route('admin.gamification.seasons.index')
            ->with('success', 'Season berhasil diperbarui!');
    }


    /**
     * Close the specified season.
     */
    public function close(Request $request, Season $season, \App\Services\SeasonService $seasonService)
    {
        $request->validate([
            'confirmation' => ['required', function ($attribute, $value, $fail) {
                if (strtoupper($value) !== 'RESET') {
                    $fail('Ketik RESET untuk mengonfirmasi penutupan season.');
                }
            }],
        ]);

        try {
            $seasonService->closeSeason($season, auth()->user());
            
            return redirect()->route('admin.gamification.seasons.index')
                ->with('success', "Season [{$season->name}] berhasil ditutup! Data Hall of Fame telah disimpan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menutup season: ' . $e->getMessage());
        }
    }

    /**
     * Activate the specified season.
     */
    public function activate(Season $season, \App\Services\SeasonService $seasonService)
    {
        try {
            $seasonService->activateSeason($season, auth()->user());

            return redirect()->route('admin.gamification.seasons.index')
                ->with('success', "Season [{$season->name}] berhasil diaktifkan kembali.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengaktifkan season: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified season.
     */
    public function destroy(Season $season)
    {
        if ($season->status === 'active') {
            return back()->with('error', "Gagal menghapus: Season [{$season->name}] sedang aktif.");
        }

        $name = $season->name;
        $season->delete();

        return redirect()->route('admin.gamification.seasons.index')
            ->with('success', "Season [{$name}] berhasil dihapus.");
    }
}
