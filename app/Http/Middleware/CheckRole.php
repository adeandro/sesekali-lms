<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user is active
        if ($user->status !== 'Aktif') {
            auth()->logout();
            return redirect()->route('login')->with('error', "Akun Anda berstatus {$user->status}, silakan hubungi admin.");
        }

        // Check if user has required role
        if (!in_array($user->role, $roles)) {
            return abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
