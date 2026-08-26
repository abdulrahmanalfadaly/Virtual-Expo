<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowGuestOrAuth
{
    /**
     * Let real authenticated users through as normal, and also let through
     * anyone who arrived via a valid guest link (see GuestAccessController).
     * Everyone else gets redirected to log in.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() || $request->session()->get('guest_access')) {
            return $next($request);
        }

        return redirect()->route('login');
    }
}
