<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordResetRequestController extends Controller
{
    public function approve(PasswordResetRequest $passwordResetRequest): RedirectResponse
    {
        if (! $passwordResetRequest->isPending()) {
            return back()->with('status', 'This request has already been handled.');
        }

        $password = $passwordResetRequest->decryptedPassword();

        if (! $password || ! $passwordResetRequest->user) {
            return back()->withErrors(['password' => 'This request can no longer be applied automatically.']);
        }

        $passwordResetRequest->user->update(['password' => Hash::make($password)]);

        $passwordResetRequest->update([
            'status' => 'approved',
            'resolved_at' => now(),
            'resolved_by' => Auth::id(),
            'requested_password' => null,
        ]);

        ActivityLogger::log(
            'admin.password_reset_approved',
            'Admin approved the password reset for '.($passwordResetRequest->school->name ?? $passwordResetRequest->email),
            $passwordResetRequest->school
        );

        return back()->with('status', 'Password updated successfully.');
    }
}
