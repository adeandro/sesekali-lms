<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        $topStudents = collect([]);
        $isLeaderboardEnabled = \App\Models\Setting::get('enable_leaderboard', '1') === '1';

        if ($isLeaderboardEnabled) {
            try {
                $topStudents = \Illuminate\Support\Facades\Cache::remember('login_leaderboard_top3', 300, function() {
                    return \App\Models\User::where('role', '=', 'student')
                        ->where('status', '=', 'Aktif')
                        ->orderBy('total_exp', 'desc')
                        ->take(3)
                        ->get()
                        ->values()
                        ->map(function ($user, $index) {
                            $name  = trim($user->full_name ?? $user->name ?? '');
                            $words = array_values(array_filter(explode(' ', $name)));
                            $initials = count($words) >= 2
                                ? strtoupper(substr($words[0],0,1).substr($words[1],0,1))
                                : strtoupper(substr($name, 0, 2));
                            $shortName = count($words) >= 2
                                ? $words[0].' '.substr($words[1], 0, 4)
                                : ($words[0] ?? 'Siswa');
                            return [
                                'rank'     => $index + 1,
                                'initials' => $initials,
                                'name'     => $shortName,
                                'points'   => number_format($user->total_exp ?? 0),
                            ];
                        });
                });
            } catch (\Throwable $e) {
                // Fallback to empty collection
            }
        }

        // ── Login-page announcements ──────────────────────────────────
        $urgentAnnouncements  = collect([]);
        $rollingAnnouncements = collect([]);
        try {
            $loginAnnouncements = \Illuminate\Support\Facades\Cache::remember('login_announcements_cache', 300, function() {
                return Announcement::forLogin()->get();
            });
            $urgentAnnouncements  = $loginAnnouncements->where('type', 'urgent')->values();
            $rollingAnnouncements = $loginAnnouncements->where('type', '!=', 'urgent')->values();
        } catch (\Throwable $e) {
            // Fail silently — announcements are non-critical
        }

        return view('auth.login', compact(
            'topStudents',
            'urgentAnnouncements',
            'rollingAnnouncements'
        ));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        // Check if input is email (contains @)
        $isEmail = str_contains($credentials['username'], '@');

        // Query user by email or Identifiers (NIS/NIP/NIY)
        $user = null;
        if ($isEmail) {
            $user = User::where('email', '=', $credentials['username'])->first();
        } else {
            // Search across all identity columns
            $user = User::where(function($query) use ($credentials) {
                $query->where('nis', '=', $credentials['username'])
                      ->orWhere('nip', '=', $credentials['username'])
                      ->orWhere('niy', '=', $credentials['username']);
            })->first();

            // Fallback: try search by email anyway if no match found in ID columns
            if (!$user) {
                $user = User::where('email', '=', $credentials['username'])->first();
            }
        }

        // Check if user exists and is active
        if (!$user || $user->status !== 'Aktif') {
            $statusStr = $user ? $user->status : 'tidak ditemukan';
            return back()->withErrors([
                'username' => "Akun Anda berstatus {$statusStr}, silakan hubungi admin.",
            ])->onlyInput('username');
        }

        // Initial login variables
        $isValid = false;
        $shouldUpgradeHash = false;

        // Fast Login Logic: Handle PLAIN_ prefix for auto-hashing
        if ($user->password && str_starts_with($user->password, 'PLAIN_')) {
            $plainFromDb = substr($user->password, 6);
            if ($credentials['password'] === $plainFromDb) {
                $isValid = true;
                $shouldUpgradeHash = true;
            }
        } elseif (Hash::check($credentials['password'], $user->password)) {
            $isValid = true;
        }

        if ($isValid) {
            // Auto-hash security upgrade if it was a PLAIN_ password (Kecuali Siswa untuk efisiensi CPU CBT)
            if ($shouldUpgradeHash && $user->role !== 'student') {
                $user->update([
                    'password' => Hash::make($credentials['password'])
                ]);
            }

            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();

            // Redirect based on role
            return $this->redirectToRoleDashboard($user);
        }

        return back()->withErrors([
            'username' => 'Invalid credentials.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }

    private function redirectToRoleDashboard(User $user)
    {
        return match ($user->role) {
            'superadmin' => redirect()->route('dashboard.superadmin'),
            'teacher'    => redirect()->route('dashboard.teacher'),
            'principal'  => redirect()->route('dashboard.principal'),
            'student'    => redirect()->route('dashboard.student'),
            'tu'         => redirect()->route('dashboard.tu'),
            default      => redirect()->route('dashboard'),
        };
    }
}
