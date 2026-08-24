<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\SiteSetting::get('site_name', 'Virtual School Expo') }}</title>
        @include('partials.favicon')
        @include('partials.social-meta')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="guest-dark flex min-h-screen flex-col items-center justify-center bg-gray-900 px-6 py-10">
            <div class="w-full overflow-hidden rounded-2xl bg-gray-800 shadow-xl sm:max-w-md">
                <div class="flex items-center justify-between gap-3 border-b border-white/10 px-6 py-5">
                    @php
                        $expoLogoPath = \App\Models\SiteSetting::get('expo_logo_path');
                        $siteName = \App\Models\SiteSetting::get('site_name', 'Virtual School Expo');
                        $navLogoHeight = \App\Models\SiteSetting::get('nav_logo_height', 48);
                        $showSiteName = \App\Models\SiteSetting::get('show_site_name_in_nav', true);
                    @endphp
                    <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2 text-lg font-semibold text-gray-100">
                        @if ($expoLogoPath)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($expoLogoPath) }}" alt="Logo" style="height: {{ $navLogoHeight }}px;" class="w-auto shrink-0 rounded-lg object-contain">
                        @else
                            <span class="flex shrink-0 items-center justify-center rounded-lg bg-indigo-500 font-bold text-white" style="height: {{ $navLogoHeight }}px; width: {{ $navLogoHeight }}px; font-size: {{ max(10, $navLogoHeight * 0.35) }}px;">VE</span>
                        @endif
                        @if ($showSiteName)
                            <span class="truncate">{{ $siteName }}</span>
                        @endif
                    </a>
                    <a href="{{ route('home') }}" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10 text-gray-200 transition hover:bg-white/20" aria-label="Back to homepage" title="Back to homepage">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </a>
                </div>

                <div class="px-6 py-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
