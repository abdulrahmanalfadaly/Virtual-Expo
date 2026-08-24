<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherAuthController extends Controller
{
    public function create(): RedirectResponse
    {
        return redirect()->route('login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        if ($user->isSchool()) {
            Auth::logout();

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'This is a school account. Please switch to the School tab to log in.',
            ]);
        }

        if (! $user->isTeacher()) {
            Auth::logout();

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        if ($user->teacher && $user->teacher->status === 'suspended') {
            Auth::logout();

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Your account has been suspended. Please contact the administrator.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home', absolute: false));
    }
}
