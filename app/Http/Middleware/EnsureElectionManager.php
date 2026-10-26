<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hanya superadmin yang boleh mengelola pilketos.
 * Spec revisi: role === 'superadmin' SAJA, bukan isAdmin().
 */
class EnsureElectionManager
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->role !== 'superadmin') {
            abort(403, 'Akses ditolak. Hanya superadmin yang dapat mengelola Pilketos.');
        }

        return $next($request);
    }
}
