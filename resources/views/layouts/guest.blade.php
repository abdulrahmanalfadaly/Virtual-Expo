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
                <div class="flex items-center justify-center border-b border-white/10 px-6 py-5">
                    @php
                        $expoLogoPath = \App\Models\SiteSetting::get('expo_logo_path');
                        $siteName = \App\Models\SiteSetting::get('site_name', 'Virtual School Expo');
                        $navLogoHeight = \App\Models\SiteSetting::get('nav_logo_height', 48);
                        $showSiteName = \App\Models\SiteSetting::get('show_site_name_in_nav', true);
                    @endphp
                    <div class="flex min-w-0 items-center gap-2 text-lg font-semibold text-gray-100">
                        @if ($expoLogoPath)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($expoLogoPath) }}" alt="Logo" style="height: {{ $navLogoHeight }}px;" class="w-auto shrink-0 rounded-lg object-contain">
                        @else
                            <span class="flex shrink-0 items-center justify-center rounded-lg bg-indigo-500 font-bold text-white" style="height: {{ $navLogoHeight }}px; width: {{ $navLogoHeight }}px; font-size: {{ max(10, $navLogoHeight * 0.35) }}px;">VE</span>
                        @endif
                        @if ($showSiteName)
                            <span class="truncate">{{ $siteName }}</span>
                        @endif
                    </div>
                </div>

                <div class="px-6 py-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
