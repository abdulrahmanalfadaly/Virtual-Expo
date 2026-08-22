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

        $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->contact_person,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'school',
            ]);

            School::create([
                'user_id' => $user->id,
                'name' => $request->school_name,
                'slug' => School::uniqueSlug($request->school_name),
                'contact_person' => $request->contact_person,
                'contact_email' => $request->email,
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        ActivityLogger::log(
            'school.registered',
            "New school registered: {$user->school->name}",
            $user->school,
            [],
            route('admin.schools.show', $user->school)
        );

        return redirect(route('school.dashboard', absolute: false));
    }
}
