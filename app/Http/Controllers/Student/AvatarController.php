<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AvatarController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get system default avatars
        $defaultAvatarsPath = public_path('images/avatars/default');
        $defaultAvatars = [];
        
        if (File::exists($defaultAvatarsPath)) {
            $files = File::files($defaultAvatarsPath);
            foreach ($files as $file) {
                $defaultAvatars[] = 'images/avatars/default/' . $file->getFilename();
            }
        }

        // FETCH DYNAMIC THEMES
        $themes = \App\Models\Theme::where('is_active', true)
            ->with('requiredAchievement')
            ->orderBy('id')
            ->get()
            ->map(function ($theme) use ($user) {
                $locked = false;
                $reason = '';

                if (!$theme->is_unlocked_by_default) {
                    $arenaThemes = [
                        'legendary-golden' => ['rank' => 1, 'name' => 'Juara 1 Battle Arena'],
                        'elite-silver' => ['rank' => 2, 'name' => 'Juara 2 Battle Arena'],
                        'master-bronze' => ['rank' => 3, 'name' => 'Juara 3 Battle Arena'],
                        'survivor-common' => ['rank' => null, 'name' => 'Partisipan Battle Arena'],
                    ];

                    if (array_key_exists($theme->slug, $arenaThemes)) {
                        $req = $arenaThemes[$theme->slug];
                        $query = \App\Models\BattleParticipant::where('user_id', $user->id);
                        if ($req['rank']) {
                            // Can be Rank 1 in individual or Rank 1 as part of a winning Fleet
                            $query->where('rank', $req['rank']);
                        }
                        $hasEarned = $query->exists();

                        if (!$hasEarned) {
                            $locked = true;
                            $reason = $req['name'];
                        }
                    } else {
                        // Normal Themes check Level (Using Global Level calculated from all-time EXP)
                        $globalLevel = floor($user->exp_total_alltime / 100) + 1;
                        if ($globalLevel < $theme->min_level) {
                            $locked = true;
                            $reason = "Level {$theme->min_level} (Global)";
                        }
                        
                        // Normal Themes check Achievement
                        if ($theme->required_achievement_id) {
                            $hasAchievement = $user->achievements()
                                ->where('achievements.id', $theme->required_achievement_id)
                                ->exists();
                            
                            if (!$hasAchievement) {
                                $locked = true;
                                $reason = $theme->requiredAchievement->title ?? "Achievement Required";
                            }
                        }
                    }
                }

                $theme->is_locked = $locked;
                $theme->lock_reason = $reason;
                return $theme;
            });

        return view('student.profile', compact('user', 'defaultAvatars', 'themes'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
            'password_display' => $request->password,
        ]);

        return back()->with('success', 'Password berhasil diperbarui!');
    }

    public function updateGallery(Request $request)
    {
        $request->validate([
            'avatar_path' => 'required|string',
        ]);

        $user = Auth::user();
        
        // Ensure path is actually within defaults to prevent injection
        if (!str_starts_with($request->avatar_path, 'images/avatars/default/')) {
            return back()->with('error', 'Pilihan avatar tidak valid.');
        }

        // We don't delete avatar_upload here as per priority logic (upload > gallery)
        // But the user might want to switch back, so choosing gallery means they have a secondary choice.

        $user->update([
            'custom_avatar' => $request->avatar_path
        ]);

        // Check for Social Media King badge
        if (\App\Models\Setting::get('enable_gamification', '1') == '1') {
            app(\App\Services\AchievementService::class)->checkAvatarAchievement($user);
        }

        return back()->with('success', 'Avatar galeri berhasil diperbarui!');
    }

    public function updateUpload(Request $request)
    {
        $request->validate([
            'avatar_file' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        $user = Auth::user();

        if ($request->hasFile('avatar_file')) {
            // Delete old uploaded avatar if exists
            if ($user->avatar_upload) {
                Storage::disk('public')->delete($user->avatar_upload);
            }

            $path = $request->file('avatar_file')->store('avatars/uploads', 'public');

            $user->update([
                'avatar_upload' => $path
            ]);

            // Check for Social Media King badge
            if (\App\Models\Setting::get('enable_gamification', '1') == '1') {
                app(\App\Services\AchievementService::class)->checkAvatarAchievement($user);
            }

            return back()->with('success', 'Foto profil berhasil diupload! Sekarang menjadi prioritas utama.');
        }

        return back()->with('error', 'Gagal mengupload foto.');
    }

    public function saveMultiavatar(Request $request)
    {
        $request->validate([
            'seed' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $seed = $request->seed;

        // Special Avatar Logic
        if ($seed === 'KingCBT') {
            if (!$user->achievements()->where('slug', 'perfect_score')->exists()) {
                return back()->with('error', 'Avatar ini terkunci! Kamu butuh achievement Perfect Score.');
            }
        }

        if ($seed === 'CyberPro') {
            if ($user->current_level < 20) {
                return back()->with('error', 'Avatar ini terkunci! Kamu butuh Level 20.');
            }
        }
        
        // Clean up old multiavatar files if they exist (legacy support)
        if ($user->custom_avatar && str_starts_with($user->custom_avatar, 'avatars/multiavatar/')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->custom_avatar);
        }

        $user->update([
            'custom_avatar' => $seed,
            'avatar_upload' => null 
        ]);

        // Check for Social Media King badge
        if (\App\Models\Setting::get('enable_gamification', '1') == '1') {
            app(\App\Services\AchievementService::class)->checkAvatarAchievement($user);
        }

        return back()->with('success', 'Avatar unik kamu berhasil disimpan!');
    }

    public function resetToFormal()
    {
        $user = Auth::user();
        
        // Delete uploaded files
        if ($user->avatar_upload) {
            Storage::disk('public')->delete($user->avatar_upload);
        }
        
        // Delete saved multiavatar file
        if ($user->custom_avatar && str_starts_with($user->custom_avatar, 'avatars/multiavatar/')) {
            Storage::disk('public')->delete($user->custom_avatar);
        }

        $user->update([
            'custom_avatar' => null,
            'avatar_upload' => null
        ]);

        return back()->with('success', 'Identitas profil telah direset ke foto formal instansi.');
    }

    public function deleteUpload()
    {
        $user = Auth::user();
        if ($user->avatar_upload) {
            Storage::disk('public')->delete($user->avatar_upload);
            $user->update(['avatar_upload' => null]);
            return back()->with('success', 'Foto upload dihapus. Kembali menggunakan avatar karakter atau foto formal.');
        }
        return back();
    }

    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|string|exists:themes,slug'
        ]);

        $user = Auth::user();
        $themeSlug = $request->theme;
        
        $theme = \App\Models\Theme::where('slug', $themeSlug)
            ->where('is_active', true)
            ->first();

        if (!$theme) {
            return $this->themeError('Tema tidak tersedia.');
        }

        // Unlock Validation
        if (!$theme->is_unlocked_by_default) {
            $arenaThemes = [
                'legendary-golden' => ['rank' => 1, 'name' => 'Juara 1 Battle Arena'],
                'elite-silver' => ['rank' => 2, 'name' => 'Juara 2 Battle Arena'],
                'master-bronze' => ['rank' => 3, 'name' => 'Juara 3 Battle Arena'],
                'survivor-common' => ['rank' => null, 'name' => 'Partisipan Battle Arena'],
            ];

            if (array_key_exists($theme->slug, $arenaThemes)) {
                $req = $arenaThemes[$theme->slug];
                $query = \App\Models\BattleParticipant::where('user_id', $user->id);
                if ($req['rank']) {
                    $query->where('rank', $req['rank']);
                }
                
                if (!$query->exists()) {
                    return $this->themeError("Tema \"{$theme->name}\" terkunci! Kamu harus menjadi {$req['name']}.");
                }
            } else {
                $globalLevel = floor($user->exp_total_alltime / 100) + 1;
                if ($globalLevel < $theme->min_level) {
                    return $this->themeError("Tema \"{$theme->name}\" terbuka di Level {$theme->min_level} (Global)!");
                }

                if ($theme->required_achievement_id) {
                    $hasAchievement = $user->achievements()
                        ->where('achievements.id', $theme->required_achievement_id)
                        ->exists();
                    
                    if (!$hasAchievement) {
                        $achievementTitle = $theme->requiredAchievement->title ?? 'Pencapaian Tertentu';
                        return $this->themeError("Tema \"{$theme->name}\" terkunci! Kamu butuh achievement '{$achievementTitle}'.");
                    }
                }
            }
        }

        $user->update(['ui_theme' => $themeSlug]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tema dashboard berhasil diperbarui!',
                'theme' => $theme
            ]);
        }

        return back()->with('success', 'Tema dashboard berhasil diperbarui!');
    }

    private function themeError($message)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }
        return back()->with('error', $message);
    }
}
