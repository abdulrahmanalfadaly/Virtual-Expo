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
        ]);

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
