<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        if ($user->isAdmin()) {
            Auth::logout();

            return redirect()->route('login')->withInput()->withErrors([
                'email' => 'Please use the admin login page.',
            ]);
        }

        if ($user->isTeacher()) {
            Auth::logout();

            return redirect()->route('login')->withInput()->withErrors([
                'email' => 'This is a teacher account. Please switch to the Teacher tab to log in.',
            ]);
        }

        if ($user->school && $user->school->status === 'suspended') {
            Auth::logout();

            return redirect()->route('login')->withInput()->withErrors([
                'email' => 'Your school account has been suspended. Please contact the administrator.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('school.dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
