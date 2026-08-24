<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'School Dashboard' }} — {{ \App\Models\SiteSetting::get('site_name', 'Virtual School Expo') }}</title>
    @include('partials.favicon')
    @include('partials.social-meta')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|fraunces:500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

    <header class="border-b border-white/10 bg-gray-950/70 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            @php
                $expoLogoPath = \App\Models\SiteSetting::get('expo_logo_path');
                $siteName = \App\Models\SiteSetting::get('site_name', 'Virtual School Expo');
                $navLogoHeight = \App\Models\SiteSetting::get('nav_logo_height', 48);
                $showSiteName = \App\Models\SiteSetting::get('show_site_name_in_nav', true);
            @endphp
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                @if ($expoLogoPath)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($expoLogoPath) }}" alt="Logo" style="height: {{ $navLogoHeight }}px;" class="w-auto rounded-lg object-contain">
                @else
                    <span class="flex items-center justify-center rounded-lg bg-indigo-500 font-bold text-white" style="height: {{ $navLogoHeight }}px; width: {{ $navLogoHeight }}px; font-size: {{ max(10, $navLogoHeight * 0.35) }}px;">VE</span>
                @endif
                @if ($showSiteName)
                    <span class="pl-3 font-display text-lg font-semibold text-white">{{ $siteName }}</span>
                @endif
            </a>

            <div class="hidden items-center gap-4 md:flex">
                <a href="{{ route('home') }}" class="text-sm font-medium text-gray-200 transition hover:text-white">
                    Home
                </a>
                <a href="{{ route('school.dashboard') }}" class="text-sm font-medium text-gray-200 transition hover:text-white">
                    Dashboard
                </a>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 text-sm font-medium text-gray-200 transition hover:text-white">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-5.5-2.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM10 12a5.99 5.99 0 00-4.793 2.39A6.483 6.483 0 0010 16.5a6.483 6.483 0 004.793-2.11A5.99 5.99 0 0010 12z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    {{ auth()->user()->school->name ?? '' }}
                </a>
            </div>
        </div>

        <div class="flex justify-center gap-6 border-t border-white/10 py-2 text-xs font-medium text-gray-300 md:hidden">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('school.dashboard') }}">Dashboard</a>
            <a href="{{ route('profile.edit') }}">Account</a>
        </div>
    </header>

    <main class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
