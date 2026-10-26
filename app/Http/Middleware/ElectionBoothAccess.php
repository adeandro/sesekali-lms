<?php

namespace App\Http\Middleware;

use App\Models\ElectionPeriod;
use App\Services\ElectionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Revised: tidak lagi memakai periodId dari URL.
 * Ambil periode aktif tunggal (ready/open) dari DB.
 * Inject ke request attributes sebagai 'election_period'.
 */
class ElectionBoothAccess
{
    public function __construct(private ElectionService $service) {}

    public function handle(Request $request, Closure $next): Response
    {
        $period = $this->service->getActivePeriod();

        if (! $period) {
            return redirect()->route('election.closed');
        }

        $request->attributes->set('election_period', $period);

        return $next($request);
    }
}
