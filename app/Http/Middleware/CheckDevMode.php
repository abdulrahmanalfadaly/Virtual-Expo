<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class CheckDevMode
{
    /**
     * Paths that stay reachable during Dev Mode — the full authentication
     * journey (register, log in, verify, reset a password, log out) always
     * has to work. Dev Mode only gates the app itself, which a user reaches
     * *after* finishing one of these.
     */
    private const EXEMPT_PATHS = [
        'login', 'register', 'logout',
        'teacher/login', 'teacher/register',
        'forgot-password',
        'reset-password', 'reset-password/*',
        'confirm-password',
        'verify-email', 'verify-email/*',
        'email/verification-notification',
        'guest/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin/*') || $request->is('up') || $request->is(self::EXEMPT_PATHS)) {
            return $next($request);
        }

        if ($request->user()?->isAdmin() || $request->session()->get('guest_access')) {
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
