<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.admin', function ($view) {
            $user = Auth::user();

            $view->with([
                'adminUnreadCount' => $user ? $user->unreadNotifications()->count() : 0,
                'adminRecentNotifications' => $user ? $user->notifications()->latest()->take(8)->get() : collect(),
            ]);
        });
    }
}
