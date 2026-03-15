<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GamificationController extends Controller
{
    // Supported criteria types — single source of truth for dropdown
    public const CRITERIA_TYPES = [
        'exam_count'          => 'Jumlah Ujian Selesai',
        'final_score'         => 'Nilai Sempurna (Satu Ujian)',
        'consecutive_pass'    => 'Lulus KKM Berturut-turut',
        'first_submit'        => 'Pertama Submit di Sesi',
        'completion_time_pct' => '% Waktu Terpakai (< nilai)',
        'score_increase'      => 'Kenaikan Nilai dari Sebelumnya',
        'submission_hour'     => 'Jam Submit ≥ nilai (WIB)',
        'custom_avatar'       => 'Menggunakan Avatar Kustom',
        'avg_score'           => 'Rata-rata Nilai Semua Ujian',
        'arena_win_count'     => 'Jumlah Menang Battle Arena (Juara 1)',
    ];

    // ─────────────────────────────────────────
    //  GLOBAL SETTINGS
    // ─────────────────────────────────────────

    public function globalSettings()
    {
        $allSettings = Setting::all()->pluck('value', 'key');
        return view('admin.gamification.settings', compact('allSettings'));
    }

    public function updateGlobalSettings(Request $request)
    {
        $request->validate([
            'enable_gamification' => 'required|in:0,1',
            'enable_leaderboard'  => 'required|in:0,1',
        ]);

        Setting::set('enable_gamification', $request->enable_gamification);
        Setting::set('enable_leaderboard', $request->enable_leaderboard);

        return redirect()->back()->with('success', 'Pengaturan gamifikasi berhasil diperbarui.');
    }

    // ─────────────────────────────────────────
    //  ACHIEVEMENT MANAGER
    // ─────────────────────────────────────────

    public function achievements()
    {
        $achievements = Achievement::orderBy('id')->get();
        return view('admin.gamification.achievements', compact('achievements'));
    }

    public function createAchievement()
    {
        $criteriaTypes = self::CRITERIA_TYPES;
        return view('admin.gamification.create-achievement', compact('criteriaTypes'));
    }

    public function storeAchievement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'lore_text' => 'nullable|string|max:1000',
            'description' => 'required|string',
            'criteria_type' => 'required|string',
            'criteria_value' => 'nullable|string',
            'xp_reward' => 'required|integer|min:0',
            'color' => 'required|string|max:20',
            'icon' => 'nullable|string|max:50',
            'icon_file' => 'nullable|image|mimes:png,jpg,jpeg,svg,gif|max:1024',
        ]);

        $baseSlug = Str::slug($request->title);
        $slug = $baseSlug;
        $counter = 1;
        
        while (Achievement::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $data = $request->except('icon_file');
        $data['slug'] = $slug;
        $data['name'] = $request->title; 
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('icon_file')) {
            $path = $request->file('icon_file')->store('achievements/icons', 'public');
            $data['icon_path'] = $path;
        }

        Achievement::create($data);

        return redirect()
            ->route('admin.gamification.achievements')
            ->with('success', "Pencapaian baru \"{$request->title}\" berhasil ditambahkan.");
    }

    public function editAchievement(Achievement $achievement)
    {
        $criteriaTypes = self::CRITERIA_TYPES;
        return view('admin.gamification.edit-achievement', compact('achievement', 'criteriaTypes'));
    }

    public function updateAchievement(Request $request, Achievement $achievement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'lore_text' => 'nullable|string|max:1000',
            'description' => 'required|string',
            'criteria_type' => 'required|string',
            'criteria_value' => 'nullable|string',
            'xp_reward' => 'required|integer|min:0',
            'color' => 'required|string|max:20',
            'icon' => 'nullable|string|max:50',
            'icon_file' => 'nullable|image|mimes:png,jpg,jpeg,svg,gif|max:1024',
        ]);

        $data = $request->except('icon_file');
        $data['name'] = $request->title;
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('icon_file')) {
            if ($achievement->icon_path) {
                Storage::disk('public')->delete($achievement->icon_path);
            }
            $data['icon_path'] = $request->file('icon_file')->store('achievements/icons', 'public');
        }

        $achievement->update($data);

        return redirect()
            ->route('admin.gamification.achievements')
            ->with('success', "Achievement \"{$achievement->display_title}\" berhasil diperbarui.");
    }

    public function destroyAchievement(Achievement $achievement)
    {
        // Detach from pivot table safely
        $achievement->users()->detach();

        // Delete icon from storage if exists
        if ($achievement->icon_path) {
            Storage::disk('public')->delete($achievement->icon_path);
        }

        $title = $achievement->display_title;
        $achievement->delete();

        return redirect()
            ->route('admin.gamification.achievements')
            ->with('success', "🗑️ Achievement \"{$title}\" beserta datanya berhasil dihapus permanen.");
    }

    // ─────────────────────────────────────────
    //  THEME MANAGER
    // ─────────────────────────────────────────

    public function themes()
    {
        $themes = \App\Models\Theme::with('requiredAchievement')->orderBy('id')->get();
        return view('admin.gamification.themes-index', compact('themes'));
    }

    public function createTheme()
    {
        $achievements = \App\Models\Achievement::orderBy('title')->get();
        return view('admin.gamification.create-theme', compact('achievements'));
    }

    public function storeTheme(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'primary_color'   => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'glow_color'      => 'required|string|max:20',
            'bg_color'        => 'required|string|max:20',
            'text_color'      => 'required|string|max:20',
            'min_level'       => 'nullable|integer|min:0',
            'required_achievement_id' => 'nullable|exists:achievements,id',
        ]);

        $slug = Str::slug($request->name);
        $baseSlug = $slug;
        $counter = 1;
        while (\App\Models\Theme::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $data = $request->all();
        $data['slug'] = $slug;
        $data['is_unlocked_by_default'] = $request->has('is_unlocked_by_default');
        $data['is_active'] = $request->has('is_active');
        $data['min_level'] = $request->min_level ?? 0;
        
        // Auto-calculate dark and surface if not provided
        $data['dark_color'] = $request->primary_color;
        $data['surface_color'] = '#ffffff';

        \App\Models\Theme::create($data);

        return redirect()
            ->route('admin.gamification.themes')
            ->with('success', "Tema \"{$request->name}\" berhasil dibuat.");
    }

    public function editTheme(\App\Models\Theme $theme)
    {
        $achievements = \App\Models\Achievement::orderBy('title')->get();
        return view('admin.gamification.edit-theme', compact('theme', 'achievements'));
    }

    public function updateTheme(Request $request, \App\Models\Theme $theme)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'primary_color'   => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'glow_color'      => 'required|string|max:20',
            'bg_color'        => 'required|string|max:20',
            'text_color'      => 'required|string|max:20',
            'min_level'       => 'nullable|integer|min:0',
            'required_achievement_id' => 'nullable|exists:achievements,id',
        ]);

        $data = $request->all();
        $data['is_unlocked_by_default'] = $request->has('is_unlocked_by_default');
        $data['is_active'] = $request->has('is_active');
        $data['min_level'] = $request->min_level ?? 0;

        $theme->update($data);

        return redirect()
            ->route('admin.gamification.themes')
            ->with('success', "Tema \"{$theme->name}\" berhasil diperbarui.");
    }

    public function destroyTheme(\App\Models\Theme $theme)
    {
        $name = $theme->name;
        $theme->delete();

        return redirect()
            ->route('admin.gamification.themes')
            ->with('success', "Tema \"{$name}\" berhasil dihapus.");
    }
}

