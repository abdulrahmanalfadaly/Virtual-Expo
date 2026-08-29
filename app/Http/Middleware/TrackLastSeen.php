<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastSeen
{
    /**
     * How long a "seen" stamp is considered fresh. One write per user per
     * window, not one per request — the point is a cheap online/offline
     * signal, not precise per-request timing.
     */
    private const THROTTLE_MINUTES = 5;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // The session is already loaded on every web request, so reading the
        // last stamp from it costs nothing. A cache/DB lookup here would add
        // a query per request, which is exactly what this guard avoids.
        $markedAt = $request->session()->get('last_seen_marked_at');

        if ($markedAt && $markedAt > now()->subMinutes(self::THROTTLE_MINUTES)->getTimestamp()) {
            return $next($request);
        }

        $request->session()->put('last_seen_marked_at', now()->getTimestamp());

        $user->forceFill(['last_seen_at' => now()])->saveQuietly();

        return $next($request);
    }
}
