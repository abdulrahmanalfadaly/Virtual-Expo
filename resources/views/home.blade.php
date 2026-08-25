<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        if (localStorage.getItem('site-theme') === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    </script>
    <title>{{ $content['site_name'] ?? 'Virtual School Expo' }}</title>
    <meta name="description" content="{{ $content['hero_description'] ?? '' }}">
    @include('partials.favicon')
    @include('partials.social-meta')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|fraunces:500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased @if (! empty($content['site_background_path'])) site-fixed-bg @endif"
    @if (! empty($content['site_background_path']))
        style="background-image: url('{{ \Illuminate\Support\Facades\Storage::disk('public')->url($content['site_background_path']) }}');"
    @endif
>

    <header class="site-nav fixed inset-x-0 top-0 z-40 border-b border-white/10 bg-gray-950/70 backdrop-blur">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            @php $navLogoHeight = $content['nav_logo_height'] ?? 48; @endphp
            <a href="#top" class="flex items-center gap-2">
                @if (! empty($content['expo_logo_path']))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($content['expo_logo_path']) }}" alt="Logo" style="height: {{ $navLogoHeight }}px;" class="w-auto rounded-lg object-contain">
                @else
                    <span class="flex items-center justify-center rounded-lg bg-indigo-500 font-bold text-white" style="height: {{ $navLogoHeight }}px; width: {{ $navLogoHeight }}px; font-size: {{ max(10, $navLogoHeight * 0.35) }}px;">VE</span>
                @endif
                @if ($content['show_site_name_in_nav'] ?? true)
                    <span class="site-nav-title pl-3 font-display text-lg font-semibold text-white">
                        {{ $content['site_name'] ?? 'Virtual School Expo' }}
                    </span>
                @endif
            </a>

            <div class="hidden items-center gap-8 text-sm font-medium text-gray-200 md:flex">
                <a href="#top" class="site-nav-link transition hover:text-white">Home</a>
                <a href="#about" class="site-nav-link transition hover:text-white">About</a>
                <a href="#schools" class="site-nav-link transition hover:text-white">Schools</a>
                <a href="#contact" class="site-nav-link transition hover:text-white">Contact</a>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    @php
                        $navUser = auth()->user();
                        $dashboardRoute = match (true) {
                            $navUser->isAdmin() => route('admin.dashboard'),
                            $navUser->isSchool() => route('school.dashboard'),
                            default => route('home'),
                        };
                        $navUserName = $navUser->isSchool() ? ($navUser->school->name ?? $navUser->name) : $navUser->name;
                        $navInitials = \Illuminate\Support\Str::of($navUserName)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                    @endphp
                    <a href="{{ $dashboardRoute }}" class="flex items-center gap-2 rounded-full bg-white py-1.5 pl-1.5 pr-4 text-sm font-semibold text-gray-900 shadow transition hover:bg-gray-100">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-500 text-xs font-bold uppercase text-white">{{ $navInitials }}</span>
                        <span class="max-w-[10rem] truncate">{{ $navUserName }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-gray-200 transition hover:bg-white/20 hover:text-white" aria-label="Log out" title="Log out">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow transition hover:bg-gray-100">
                        Login
                    </a>
                @endauth
            </div>
        </nav>

        <div class="flex justify-center gap-6 border-t border-white/10 py-2 text-xs font-medium text-gray-300 md:hidden">
            <a href="#about" class="site-nav-link">About</a>
            <a href="#schools" class="site-nav-link">Schools</a>
            <a href="#contact" class="site-nav-link">Contact</a>
        </div>
    </header>

    <main>
        <section id="top" class="hero-gradient relative flex min-h-screen items-center overflow-hidden pt-24"
                 style="--hero-overlay-opacity: {{ ($content['hero_overlay_opacity'] ?? 70) / 100 }};">
            <div class="relative z-10 mx-auto grid max-w-6xl grid-cols-1 items-center gap-12 px-6 lg:grid-cols-2 lg:gap-16">
                <div class="flex justify-center lg:justify-end">
                    @if (! empty($content['expo_logo_path']))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($content['expo_logo_path']) }}" alt="Logo" class="w-full max-w-xs object-contain drop-shadow-2xl sm:max-w-sm lg:max-w-md">
                    @else
                        <span class="flex h-48 w-48 items-center justify-center rounded-3xl bg-indigo-500 text-6xl font-bold text-white shadow-2xl sm:h-56 sm:w-56 lg:h-64 lg:w-64">VE</span>
                    @endif
                </div>

                <div class="text-center lg:text-left">
                    <h1 class="font-display text-4xl font-semibold leading-tight text-white sm:text-6xl">
                        {{ $content['hero_headline'] ?? 'Discover Your Future School' }}
                    </h1>
                    @if (! empty($content['hero_description']))
                        <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-300 lg:mx-0">{{ $content['hero_description'] }}</p>
                    @endif
                    <div class="mt-10 flex flex-wrap items-center justify-center gap-4 lg:justify-start">
                        <a href="#schools" class="rounded-full bg-indigo-500 px-7 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                            Explore Booths
                        </a>
                    </div>
                </div>
            </div>

            <a href="#about" class="absolute bottom-8 left-1/2 z-10 -translate-x-1/2 text-gray-400 transition hover:text-white" aria-label="Scroll down">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 animate-bounce" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </section>

        <section id="about" class="bg-white py-24">
            <div class="mx-auto max-w-3xl px-6 text-center">
                <h2 class="font-display text-3xl font-semibold text-gray-900 sm:text-4xl">About the Expo</h2>
                @if (! empty($content['about_content']))
                    <p class="mt-6 whitespace-pre-line text-lg leading-relaxed text-gray-600">{{ $content['about_content'] }}</p>
                @endif
            </div>
        </section>

        <section id="schools" class="py-24">
            <div class="mx-auto max-w-7xl px-6">
                <div class="site-schools-panel mx-auto max-w-3xl rounded-3xl bg-gray-950/70 px-6 py-10 text-center shadow-xl backdrop-blur-sm sm:px-10 sm:py-14">
                    <h2 class="site-schools-heading font-display text-4xl font-bold tracking-tight text-white sm:text-6xl">
                        {{ $content['schools_heading_prefix'] ?? 'Welcome to the' }} <span class="site-gradient-text bg-gradient-to-r from-indigo-300 to-pink-300 bg-clip-text text-transparent">{{ $content['schools_heading_highlight'] ?? 'Virtual School Expo' }}</span>
                    </h2>
                    <p class="site-schools-subtitle mt-4 text-gray-200">Click on any booth to explore programs, media, and get in touch directly.</p>
                </div>

                @if ($schools->isEmpty())
                    <p class="site-schools-empty mt-16 text-center text-gray-500">No school booths are published yet. Please check back soon.</p>
                @else
                    <div class="booth-grid mx-auto mt-20"
                         style="--booth-cols: {{ $boothGrid['booth_grid_columns'] ?? 2 }}; --booth-gap: {{ $boothGrid['booth_grid_gap'] ?? 2.5 }}rem;">
                        @foreach ($schools as $school)
                            <x-booth-card :school="$school" :booth-settings="$boothSettings" />
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section id="contact" class="bg-white py-24">
            <div class="mx-auto max-w-3xl px-6 text-center">
                <h2 class="font-display text-3xl font-semibold text-gray-900 sm:text-4xl">Contact &amp; Support</h2>
                @if (! empty($content['support_info']))
                    <p class="mt-4 text-gray-600">{{ $content['support_info'] }}</p>
                @endif
                <div class="mt-8 grid gap-4 text-sm text-gray-700 sm:grid-cols-2">
                    @if (! empty($content['contact_email']))
                        <div class="rounded-xl bg-gray-50 p-5">
                            <p class="font-semibold text-gray-900">Email</p>
                            <a href="mailto:{{ $content['contact_email'] }}" class="mt-1 block text-indigo-600 hover:underline">{{ $content['contact_email'] }}</a>
                        </div>
                    @endif
                    @if (! empty($content['contact_phone']))
                        <div class="rounded-xl bg-gray-50 p-5">
                            <p class="font-semibold text-gray-900">Phone / WhatsApp</p>
                            <p class="mt-1">{{ $content['contact_phone'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-gray-950 py-10 text-center text-sm text-gray-400">
        <p>{{ $content['footer_text'] ?? '© '.date('Y').' Virtual School Expo. All rights reserved.' }}</p>
    </footer>

    @foreach ($schools as $school)
        <x-booth-modal :school="$school" :allow-applications="$allowApplications" :modal-opacity="$boothModalOpacity ?? 90" :applied-at="$existingApplications->get($school->id)" />
    @endforeach

    <script>
        document.getElementById('theme-toggle')?.addEventListener('click', () => {
            const root = document.documentElement;
            const isLight = root.getAttribute('data-theme') === 'light';
            root.setAttribute('data-theme', isLight ? 'dark' : 'light');
            localStorage.setItem('site-theme', isLight ? 'dark' : 'light');
            document.getElementById('theme-icon-moon').classList.toggle('hidden', isLight);
            document.getElementById('theme-icon-sun').classList.toggle('hidden', ! isLight);
        });

        if (document.documentElement.getAttribute('data-theme') === 'light') {
            document.getElementById('theme-icon-moon')?.classList.add('hidden');
            document.getElementById('theme-icon-sun')?.classList.remove('hidden');
        }
    </script>
</body>
</html>
