<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class CheckDevMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin/*') || $request->is('up')) {
            return $next($request);
        }

        if ($request->user()?->isAdmin()) {
            return $next($request);
        }

        if (! SiteSetting::get('dev_mode_enabled', false)) {
            return $next($request);
        }

        $endsAt = SiteSetting::get('dev_mode_ends_at');

        if ($endsAt && now()->greaterThanOrEqualTo(Carbon::parse($endsAt))) {
            SiteSetting::set('dev_mode_enabled', false);
            SiteSetting::set('dev_mode_ends_at', null);

            return $next($request);
        }

        return response()->view('dev-mode', [
            'message' => SiteSetting::get('dev_mode_message') ?: 'We\'re making some updates behind the scenes. Please check back soon.',
            'endsAt' => $endsAt,
        ], 503);
    }
}
