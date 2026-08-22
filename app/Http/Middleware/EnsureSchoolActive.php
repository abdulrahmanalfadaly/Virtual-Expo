<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $school = $user?->school;

        if ($school && $school->status === 'suspended') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your school account has been suspended. Please contact the administrator.',
            ]);
        }

        return $next($request);
    }
}
