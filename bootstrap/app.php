<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'school.active' => \App\Http\Middleware\EnsureSchoolActive::class,
            'teacher.active' => \App\Http\Middleware\EnsureTeacherActive::class,
            'guest_or_auth' => \App\Http\Middleware\AllowGuestOrAuth::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\CheckDevMode::class,
        ]);

        // Laravel's middleware priority sorting runs the "auth" middleware before
        // any custom middleware by default, regardless of group order. Dev Mode
        // must run first so it can gate a page before "auth" ever redirects.
        // Note: the priority list anchors on the AuthenticatesRequests *contract*,
        // not the concrete Authenticate class — anchoring on the concrete class
        // silently no-ops the prepend (falls through to appending at the end).
        $middleware->prependToPriorityList(
            before: \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            prepend: \App\Http\Middleware\CheckDevMode::class,
        );

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin/*')) {
                return route('admin.login');
            }

            if ($request->is('school/*')) {
                return route('login', ['as' => 'school']);
            }

            return route('login');
        });

        $middleware->redirectUsersTo(function ($request) {
            $user = auth()->user();

            return match (true) {
                $user?->isAdmin() => route('admin.dashboard'),
                $user?->isSchool() => route('school.dashboard'),
                default => route('home'),
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
