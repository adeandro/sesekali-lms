<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        // Check if input is email (contains @) or NIS (numeric)
        $isEmail = str_contains($credentials['username'], '@');
        $isNIS = is_numeric($credentials['username']) && strlen($credentials['username']) > 3;

        // Query user by email or NIS
        $user = null;
        if ($isEmail) {
            $user = User::where('email', $credentials['username'])->first();
        } elseif ($isNIS) {
            $user = User::where('nis', $credentials['username'])->first();
        } else {
            // Try email as fallback if neither email nor NIS format
            $user = User::where('email', $credentials['username'])->first();
        }

        // Check if user exists and is active
        if (!$user || !$user->is_active) {
            return back()->withErrors([
                'username' => 'Invalid credentials or account is deactivated.',
            ])->onlyInput('username');
        }

        // Attempt to login with password
        if (Hash::check($credentials['password'], $user->password)) {
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
            'admin' => redirect()->route('dashboard.admin'),
            'student' => redirect()->route('dashboard.student'),
            default => redirect()->route('dashboard'),
        };
    }
}
