<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SchoolPasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('email', $data['email'])->where('role', 'school')->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'We could not find a school account with that email.',
            ]);
        }

        $school = $user->school;

        $resetRequest = PasswordResetRequest::create([
            'user_id' => $user->id,
            'school_id' => $school?->id,
            'email' => $data['email'],
            'requested_password' => $data['password'],
            'status' => 'pending',
        ]);

        ActivityLogger::log(
            'school.password_reset_requested',
            ($school->name ?? $user->name).' requested a password reset',
            $school,
            ['password_reset_request_id' => $resetRequest->id],
            route('admin.activity.index')
        );

        return back()->with('status', 'Your request has been submitted. An administrator will review it and update your password shortly.');
    }
}
