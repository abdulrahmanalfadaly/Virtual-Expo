<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} — {{ \App\Models\SiteSetting::get('site_name', 'Virtual School Expo') }}</title>
    @include('partials.favicon')
    @include('partials.social-meta')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900" x-data="{ sidebarOpen: false, bellOpen: false }">

    <div class="flex min-h-screen">
        <aside class="fixed inset-y-0 left-0 z-30 w-64 -translate-x-full bg-gray-900 text-gray-200 transition-transform lg:static lg:translate-x-0"
               :class="{ '!translate-x-0': sidebarOpen }">
            <div class="flex h-16 items-center gap-2 px-6">
                @php
                    $adminLogoPath = \App\Models\SiteSetting::get('expo_logo_path');
                    $adminSiteName = \App\Models\SiteSetting::get('site_name', 'Virtual School Expo');
                @endphp
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold text-white">
                    @if ($adminLogoPath)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($adminLogoPath) }}" alt="Logo" class="h-8 w-8 rounded-lg object-contain">
                    @else
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500 text-xs font-bold text-white">VE</span>
                    @endif
                    <span>{{ $adminSiteName }}</span>
                </a>
            </div>
            <nav class="mt-4 space-y-1 px-3 text-sm">
                @php
                    $links = [
                        ['route' => 'admin.dashboard', 'label' => 'Dashboard'],
                        ['route' => 'admin.schools.index', 'label' => 'Schools'],
                        ['route' => 'admin.applications.index', 'label' => 'Applications'],
                        ['route' => 'admin.homepage.edit', 'label' => 'Homepage Content'],
                        ['route' => 'admin.booth-settings.edit', 'label' => 'Booth Settings'],
                        ['route' => 'admin.activity.index', 'label' => 'Activity Log'],
                    ];
                @endphp
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}"
                       class="block rounded-lg px-3 py-2 transition {{ request()->routeIs($link['route']) ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="absolute bottom-0 w-full p-3">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="block w-full rounded-lg px-3 py-2 text-left text-sm text-gray-300 hover:bg-gray-800 hover:text-white">Log Out</button>
                </form>
            </div>
        </aside>

        <div class="flex-1">
            <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6">
                <button class="lg:hidden" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-900">{{ $title ?? 'Dashboard' }}</h1>

                <div class="relative" @click.outside="bellOpen = false">
                    <button @click="bellOpen = !bellOpen" class="relative rounded-full p-2 text-gray-500 hover:bg-gray-100" aria-label="Notifications">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span id="notif-badge" class="absolute -right-0.5 -top-0.5 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs font-semibold text-white {{ $adminUnreadCount ?? 0 ? '' : 'hidden' }}">
                            {{ $adminUnreadCount ?? 0 }}
                        </span>
                    </button>

                    <div x-show="bellOpen" x-cloak class="absolute right-0 z-40 mt-2 w-80 rounded-xl border border-gray-200 bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                            <span class="text-sm font-semibold text-gray-900">Notifications</span>
                            <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                                @csrf
                                <button class="text-xs font-medium text-indigo-600 hover:underline">Mark all read</button>
                            </form>
                        </div>
                        <div id="notif-list" class="max-h-96 overflow-y-auto scrollbar-thin">
                            @forelse ($adminRecentNotifications ?? [] as $notification)
                                <a href="{{ route('admin.notifications.read', $notification->id) }}"
                                   class="block border-b border-gray-50 px-4 py-3 text-sm hover:bg-gray-50 {{ $notification->read_at ? 'opacity-60' : '' }}">
                                    <p class="text-gray-800">{{ $notification->data['description'] ?? 'Notification' }}</p>
                                    <p class="mt-1 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                                </a>
                            @empty
                                <p class="px-4 py-6 text-center text-sm text-gray-400">No notifications yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
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
        </div>
    </div>

    <div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-3"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (! window.Echo) return;

            window.Echo.private('admin-notifications').listen('.admin.notification', (e) => {
                const badge = document.getElementById('notif-badge');
                badge.textContent = (parseInt(badge.textContent || '0', 10) + 1).toString();
                badge.classList.remove('hidden');

                const list = document.getElementById('notif-list');
                const item = document.createElement('a');
                item.href = e.url || '{{ route('admin.dashboard') }}';
                item.className = 'block border-b border-gray-50 px-4 py-3 text-sm hover:bg-gray-50';
                item.innerHTML = `<p class="text-gray-800">${e.description}</p><p class="mt-1 text-xs text-gray-400">just now</p>`;
                list.prepend(item);

                const toast = document.createElement('div');
                toast.className = 'animate-fade-in w-80 rounded-xl border border-gray-200 bg-white p-4 shadow-2xl';
                toast.innerHTML = `<p class="text-sm font-semibold text-gray-900">New Activity</p><p class="mt-1 text-sm text-gray-600">${e.description}</p>`;
                document.getElementById('toast-container').appendChild(toast);
                setTimeout(() => toast.remove(), 6000);
            });
        });
    </script>
</body>
</html>
