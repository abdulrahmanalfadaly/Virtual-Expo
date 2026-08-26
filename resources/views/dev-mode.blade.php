<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="60">

    <title>{{ \App\Models\SiteSetting::get('site_name', 'Virtual School Expo') }}</title>
    @include('partials.favicon')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|fraunces:500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    @php
        $logoPath = \App\Models\SiteSetting::get('expo_logo_path');
        $logoHeight = max((int) \App\Models\SiteSetting::get('nav_logo_height', 64), 56);
    @endphp

    <div class="hero-gradient relative flex min-h-screen items-center justify-center overflow-hidden px-6 text-center">
        <div class="relative z-10 max-w-lg">
            <div class="flex justify-center">
                @if ($logoPath)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) }}" alt="Logo" style="height: {{ $logoHeight }}px;" class="w-auto object-contain drop-shadow-xl">
                @else
                    <span class="flex items-center justify-center rounded-2xl bg-indigo-500 font-bold text-white shadow-xl" style="height: {{ $logoHeight }}px; width: {{ $logoHeight }}px; font-size: {{ $logoHeight * 0.35 }}px;">VE</span>
                @endif
            </div>

            <h1 class="mt-8 font-display text-3xl font-semibold text-white sm:text-4xl">We'll be right back</h1>
            <p class="mx-auto mt-4 max-w-md text-lg text-gray-300">{{ $message }}</p>

            @if ($endsAt)
                <div class="mt-8 inline-flex items-center gap-2 rounded-full bg-white/10 px-5 py-2.5 text-sm font-medium text-gray-200 ring-1 ring-white/10" data-countdown="{{ \Illuminate\Support\Carbon::parse($endsAt)->toIso8601String() }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span data-countdown-text>Calculating...</span>
                </div>
            @endif
        </div>
    </div>

    @if ($endsAt)
        <script>
            (function () {
                const el = document.querySelector('[data-countdown]');
                if (! el) return;

                const target = new Date(el.dataset.countdown).getTime();
                const textEl = el.querySelector('[data-countdown-text]');

                function tick() {
                    const diff = target - Date.now();

                    if (diff <= 0) {
                        textEl.textContent = 'Back any moment now...';
                        window.location.reload();
                        return;
                    }

                    const h = Math.floor(diff / 3600000);
                    const m = Math.floor((diff % 3600000) / 60000);
                    const s = Math.floor((diff % 60000) / 1000);
                    textEl.textContent = 'Back in '
                        + String(h).padStart(2, '0') + ':'
                        + String(m).padStart(2, '0') + ':'
                        + String(s).padStart(2, '0');
                }

                tick();
                setInterval(tick, 1000);
            })();
        </script>
    @endif
</body>
</html>
