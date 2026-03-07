<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class UserPreferenceController extends Controller
{
    /**
     * Allowed themes per role.
     * Students get the full gamified 9-theme palette.
     * Admin / Teacher / SuperAdmin get 3 formal/calm themes only.
     */
    private const STUDENT_THEMES = [
        'indigo', 'emerald', 'rose', 'amber', 'violet',
        'midnight', 'cyberpunk', 'volcano', 'ocean', 'slate',
    ];

    private const PRO_THEMES = ['slate', 'indigo', 'ocean'];

    /**
     * Level-lock requirements for Student themes.
     */
    private const LEVEL_LOCKS = [
        'emerald'   => 5,
        'volcano'   => 15,
        'rose'      => 25,
        'amber'     => 35,
        'midnight'  => 45,
    ];

    /**
     * Achievement-lock requirements for Student themes.
     */
    private const ACHIEVEMENT_LOCKS = [
        'cyberpunk' => 'the_flash',
    ];

    /**
     * Universal theme update — handles all roles.
     * POST /profile/update-theme
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $role = $user->role;
        $theme = $request->input('theme');

        // 1. Determine allowed themes based on role
        $allowedThemes = in_array($role, ['superadmin', 'teacher'])
            ? self::PRO_THEMES
            : self::STUDENT_THEMES;

        // 2. Validate theme exists in allowed list
        $request->validate([
            'theme' => 'required|in:' . implode(',', $allowedThemes),
        ]);

        // 3. For Students: check gamification kill switch
        if ($role === 'student') {
            $gamificationEnabled = Setting::get('enable_gamification', '1') == '1';

            if (!$gamificationEnabled) {
                // Force Indigo when gamification is disabled
                $theme = 'indigo';
            } else {
                // Apply level-lock rules
                $level = $user->current_level ?? 1;
                if (isset(self::LEVEL_LOCKS[$theme]) && $level < self::LEVEL_LOCKS[$theme]) {
                    return $this->themeError(
                        'Tema ' . ucfirst($theme) . ' terbuka di Level ' . self::LEVEL_LOCKS[$theme] . '!'
                    );
                }

                // Apply achievement-lock rules
                if (isset(self::ACHIEVEMENT_LOCKS[$theme])) {
                    $slug = self::ACHIEVEMENT_LOCKS[$theme];
                    if (!$user->achievements()->where('slug', $slug)->exists()) {
                        return $this->themeError(
                            'Tema ' . ucfirst($theme) . " terkunci! Kamu butuh achievement khusus."
                        );
                    }
                }
            }
        }

        // 4. Persist theme for ALL roles
        $user->update(['ui_theme' => $theme]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tema berhasil diperbarui!',
                'theme'   => $theme,
            ]);
        }

        return back()->with('success', 'Tema dashboard berhasil diperbarui!');
    }

    /**
     * Return a themed error response.
     */
    private function themeError(string $message)
    {
        if (request()->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }
        return back()->with('error', $message);
    }
}
