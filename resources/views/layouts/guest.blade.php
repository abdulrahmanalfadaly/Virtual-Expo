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
        <div class="guest-dark min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-900">
            <div class="w-full px-6 sm:max-w-md">
                <a href="{{ route('home') }}" class="flex items-center justify-center gap-2 text-lg font-semibold text-gray-200">
                    @php
                        $expoLogoPath = \App\Models\SiteSetting::get('expo_logo_path');
                        $siteName = \App\Models\SiteSetting::get('site_name', 'Virtual School Expo');
                    @endphp
                    @if ($expoLogoPath)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($expoLogoPath) }}" alt="Logo" class="h-8 w-8 rounded-lg object-contain">
                    @else
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500 text-xs font-bold text-white">VE</span>
                    @endif
                    {{ $siteName }}
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
