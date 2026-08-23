<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (! SiteSetting::get('allow_registration', true)) {
            return redirect()->route('home')->withErrors([
                'registration' => 'School registration is currently closed. Please contact the administrator.',
            ]);
        }

        return view('auth.register');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (! SiteSetting::get('allow_registration', true)) {
            abort(403, 'Registration is currently closed.');
        }

        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'school_type' => ['required', 'in:national,international,online'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'full_description' => ['nullable', 'string', 'max:5000'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'zoom_url' => ['nullable', 'url', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $requiresApproval = SiteSetting::get('require_admin_approval', false);

        $user = DB::transaction(function () use ($request, $data, $requiresApproval) {
            $user = User::create([
                'name' => $data['contact_person'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'school',
            ]);

            $logoPath = $request->hasFile('logo')
                ? $request->file('logo')->store('logos', 'public')
                : null;

            School::create([
                'user_id' => $user->id,
                'name' => $data['school_name'],
                'school_type' => $data['school_type'],
                'slug' => School::uniqueSlug($data['school_name']),
                'contact_person' => $data['contact_person'],
                'contact_email' => $data['email'],
                'logo_path' => $logoPath,
                'full_description' => $data['full_description'] ?? null,
                'video_url' => $data['video_url'] ?? null,
                'zoom_url' => $data['zoom_url'] ?? null,
                'is_published' => ! $requiresApproval,
                'approved_at' => $requiresApproval ? null : now(),
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        ActivityLogger::log(
            'school.registered',
            "New school registered: {$user->school->name}".($requiresApproval ? ' (pending admin approval)' : ''),
            $user->school,
            [],
            route('admin.schools.show', $user->school)
        );

        return redirect(route('school.dashboard', absolute: false));
    }
}
