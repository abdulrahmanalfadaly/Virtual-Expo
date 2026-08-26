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
    @endphp

    <div class="hero-gradient relative flex min-h-screen items-center justify-center overflow-hidden px-6 text-center">
        <div class="relative z-10 w-full max-w-4xl">
            <div class="flex flex-col items-center gap-8 sm:flex-row sm:items-center sm:text-left">
                <div class="shrink-0">
                    @if ($logoPath)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) }}" alt="Logo" class="h-24 w-auto rounded-2xl object-contain shadow-2xl sm:h-32 md:h-40">
                    @else
                        <span class="flex h-24 w-24 items-center justify-center rounded-2xl bg-indigo-500 text-3xl font-bold text-white shadow-2xl sm:h-32 sm:w-32 sm:text-4xl md:h-40 md:w-40 md:text-5xl">VE</span>
                    @endif
                </div>

                <p class="max-w-xl text-2xl font-medium leading-snug text-white sm:text-3xl">{{ $message }}</p>
            </div>

            @if ($endsAt)
                <div class="countdown-wrap mt-12 flex items-center justify-center gap-3 sm:gap-5" data-countdown="{{ \Illuminate\Support\Carbon::parse($endsAt)->toIso8601String() }}">
                    <div class="flex flex-col items-center">
                        <span class="countdown-digit flex h-20 w-20 items-center justify-center rounded-2xl bg-white/10 font-display text-3xl font-bold text-white ring-1 ring-white/15 backdrop-blur-sm sm:h-28 sm:w-28 sm:text-5xl md:h-32 md:w-32 md:text-6xl" data-unit="hours">00</span>
                        <span class="mt-3 text-xs font-semibold uppercase tracking-widest text-gray-400 sm:text-sm">Hours</span>
                    </div>
                    <span class="countdown-colon font-display text-3xl font-bold text-indigo-400 sm:text-5xl md:text-6xl">:</span>
                    <div class="flex flex-col items-center">
                        <span class="countdown-digit flex h-20 w-20 items-center justify-center rounded-2xl bg-white/10 font-display text-3xl font-bold text-white ring-1 ring-white/15 backdrop-blur-sm sm:h-28 sm:w-28 sm:text-5xl md:h-32 md:w-32 md:text-6xl" data-unit="minutes">00</span>
                        <span class="mt-3 text-xs font-semibold uppercase tracking-widest text-gray-400 sm:text-sm">Minutes</span>
                    </div>
                    <span class="countdown-colon font-display text-3xl font-bold text-indigo-400 sm:text-5xl md:text-6xl">:</span>
                    <div class="flex flex-col items-center">
                        <span class="countdown-digit flex h-20 w-20 items-center justify-center rounded-2xl bg-white/10 font-display text-3xl font-bold text-white ring-1 ring-white/15 backdrop-blur-sm sm:h-28 sm:w-28 sm:text-5xl md:h-32 md:w-32 md:text-6xl" data-unit="seconds">00</span>
                        <span class="mt-3 text-xs font-semibold uppercase tracking-widest text-gray-400 sm:text-sm">Seconds</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($endsAt)
        <script>
            (function () {
                const wrap = document.querySelector('.countdown-wrap');
                if (! wrap) return;

                const target = new Date(wrap.dataset.countdown).getTime();
                const digits = {
                    hours: wrap.querySelector('[data-unit="hours"]'),
                    minutes: wrap.querySelector('[data-unit="minutes"]'),
                    seconds: wrap.querySelector('[data-unit="seconds"]'),
                };
                const previous = { hours: null, minutes: null, seconds: null };

                function setDigit(el, value, key) {
                    const text = String(value).padStart(2, '0');
                    if (previous[key] === text) return;
                    previous[key] = text;
                    el.textContent = text;
                    el.classList.remove('countdown-pulse');
                    void el.offsetWidth;
                    el.classList.add('countdown-pulse');
                }

                function tick() {
                    const diff = target - Date.now();

                    if (diff <= 0) {
                        setDigit(digits.hours, 0, 'hours');
                        setDigit(digits.minutes, 0, 'minutes');
                        setDigit(digits.seconds, 0, 'seconds');
                        window.location.reload();
                        return;
                    }

                    setDigit(digits.hours, Math.floor(diff / 3600000), 'hours');
                    setDigit(digits.minutes, Math.floor((diff % 3600000) / 60000), 'minutes');
                    setDigit(digits.seconds, Math.floor((diff % 60000) / 1000), 'seconds');
                }

                tick();
                setInterval(tick, 1000);
            })();
        </script>
    @endif
</body>
</html>
