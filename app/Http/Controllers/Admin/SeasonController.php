<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistoricalWinner;
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
        $seasons = Season::orderByDesc('start_date')->paginate(10);
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
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'is_active'     => 'boolean',
        ]);

        // Only one season can be active at a time
        if (!empty($validated['is_active'])) {
            Season::where('is_active', true)->update(['is_active' => false]);
        }

        Season::create($validated);

        return redirect()->route('admin.gamification.seasons.index')
            ->with('success', "Season [{$validated['name']}] berhasil dibuat!");
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
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'is_active'     => 'boolean',
        ]);

        if (!empty($validated['is_active']) && !$season->is_active) {
            Season::where('is_active', true)->update(['is_active' => false]);
        }

        $season->update($validated);

        return redirect()->route('admin.gamification.seasons.index')
            ->with('success', 'Season berhasil diperbarui!');
    }

    // ── Trigger Reset (snapshot + seasonal_exp = 0) ──────────────────────

    public function triggerReset(Season $season)
    {
        if ($season->reset_done) {
            return back()->with('error', "Reset sudah pernah dijalankan untuk season ini pada {$season->reset_executed_at->format('d M Y H:i')}.");
        }

        // Run via Artisan in-process
        $exitCode = Artisan::call('season:reset', ['season_id' => $season->id]);

        if ($exitCode === 0) {
            // Clear leaderboard cache after reset
            Cache::flush();
            return redirect()->route('admin.gamification.seasons.index')
                ->with('success', "✅ Seasonal EXP di-reset! Snapshot Hall of Fame tersimpan untuk [{$season->name}].");
        }

        return back()->with('error', 'Reset gagal. Cek application log.');
    }

    // ── Migration Dashboard ───────────────────────────────────────────────

    public function migrationDashboard(Season $season)
    {
        $grade12 = User::where('role', 'student')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('grade_level', 12)->orWhere('grade', '12');
            })
            ->orderBy('name')
            ->get();

        $gradeToPromote = User::where('role', 'student')
            ->where('is_active', true)
            ->whereIn('grade_level', [10, 11])
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        return view('admin.gamification.seasons.migration', compact('season', 'grade12', 'gradeToPromote'));
    }

    // ── Execute Migration ─────────────────────────────────────────────────

    public function executeMigration(Request $request, Season $season)
    {
        if ($season->migration_done) {
            return back()->with('error', "Migrasi sudah dijalankan pada {$season->migration_executed_at->format('d M Y H:i')}.");
        }

        $request->validate([
            'stay_behind_ids'   => 'nullable|array',
            'stay_behind_ids.*' => 'integer|exists:users,id',
            'academic_year'     => 'required|regex:/^\d{4}\/\d{4}$/',
            'confirm'           => 'required|in:KONFIRMASI',
        ], [
            'confirm.in' => 'Ketik KONFIRMASI untuk melanjutkan proses migrasi.',
        ]);

        $stayBehindIds = $request->input('stay_behind_ids', []);
        $academicYear  = $request->input('academic_year', $season->academic_year);

        $exitCode = Artisan::call('season:migrate', [
            '--stay-behind'   => $stayBehindIds,
            '--academic-year' => $academicYear,
        ]);

        if ($exitCode === 0) {
            $season->update([
                'migration_done'        => true,
                'migration_executed_at' => now(),
            ]);

            return redirect()->route('admin.gamification.seasons.index')
                ->with('success', "🎓 Grand Migration selesai! Tahun Ajaran {$academicYear} telah diarsipkan.");
        }

        return back()->with('error', 'Migrasi gagal. Cek application log.');
    }
}
