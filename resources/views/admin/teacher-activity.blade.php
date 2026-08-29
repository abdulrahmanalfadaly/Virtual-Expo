@php
    $isLiveRange = $range['key'] === 'live';
    $metrics = $metrics ?? null;
@endphp

<x-admin-layout title="Teacher Activity">
    <style>
        .viz {
            --s1: #2a78d6;
            --s2: #eb6834;
            --s3: #1baf7a;
            --ink: #0b0b0b;
            --ink-2: #52514e;
            --muted: #898781;
            --grid: #e1e0d9;
            --axis: #c3c2b7;
            --good: #006300;
        }
        .viz-chart { width: 100%; height: auto; display: block; overflow: visible; }
        .viz-chart path, .viz-chart line, .viz-chart circle { vector-effect: non-scaling-stroke; }
        .viz-tip {
            position: absolute; pointer-events: none; opacity: 0; transition: opacity .12s;
            background: #fff; border: 1px solid rgba(11,11,11,.10);
            box-shadow: 0 6px 20px rgba(11,11,11,.10); border-radius: 10px;
            padding: 8px 10px; font-size: 12px; z-index: 20; min-width: 130px;
        }
        .viz-tip[data-show="1"] { opacity: 1; }
        .viz-seg { transition: filter .12s; }
        .viz-seg:hover { filter: brightness(1.12); }
        .viz-pulse {
            width: 9px; height: 9px; border-radius: 9999px; background: var(--good);
            box-shadow: 0 0 0 0 rgba(12,163,12,.55); animation: vizPulse 2s infinite;
        }
        @keyframes vizPulse {
            0% { box-shadow: 0 0 0 0 rgba(12,163,12,.55); }
            70% { box-shadow: 0 0 0 9px rgba(12,163,12,0); }
            100% { box-shadow: 0 0 0 0 rgba(12,163,12,0); }
        }
        .range-tab {
            position: relative; display: flex; flex-direction: column; gap: .25rem;
            border-radius: 1rem; padding: 1rem 1.15rem; text-align: left;
            border: 1px solid #e5e7eb; background: #fff; transition: all .16s ease;
        }
        .range-tab:hover { border-color: #c7d2fe; transform: translateY(-2px); box-shadow: 0 8px 20px -12px rgba(30,41,59,.35); }
        .range-tab[aria-current="true"] {
            background: #111827; border-color: #111827; color: #fff;
            box-shadow: 0 12px 24px -14px rgba(17,24,39,.8);
        }
        .range-tab[aria-current="true"] .range-hint { color: #9ca3af; }
        @media (prefers-reduced-motion: reduce) {
            .viz-pulse { animation: none; }
            .range-tab:hover { transform: none; }
        }
    </style>

    <div class="viz space-y-6">

        {{-- Expo status — everything in the organiser's own timezone ----------}}
        @if ($expo->isConfigured())
            <div class="overflow-hidden rounded-2xl bg-gray-900 text-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-5">
                    <div>
                        <div class="flex items-center gap-2">
                            @if ($expo->isLive())
                                <span class="viz-pulse"></span>
                            @endif
                            <span class="text-xs font-semibold uppercase tracking-wider {{ $expo->isLive() ? 'text-emerald-400' : 'text-gray-400' }}">
                                {{ $expo->statusLabel() }}
                            </span>
                        </div>
                        <p class="mt-1 text-2xl font-semibold">
                            @if ($expo->hasEnded())
                                Expo finished
                            @elseif ($expo->hasStarted())
                                Day {{ $expo->currentDay() }} of {{ $expo->days }}
                            @else
                                Opens {{ $expo->firstStart()->format('D j M') }}
                            @endif
                        </p>
                        <p class="mt-1 text-sm text-gray-400">
                            {{ $expo->scheduleLabel() }} · {{ $expo->days }} {{ \Illuminate\Support\Str::plural('day', $expo->days) }}, round the clock
                            <span class="text-gray-500">· {{ $expo->timezone }} ({{ $expo->timezoneLabel() }})</span>
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-xs uppercase tracking-wider text-gray-400">
                            {{ $expo->isLive() ? 'Closes in' : ($expo->hasEnded() ? 'Total time live' : 'Opens in') }}
                        </p>
                        <p class="mt-1 text-2xl font-semibold"
                           @if ($expo->boundaryTarget()) data-expo-countdown="{{ $expo->boundaryTarget()->toIso8601String() }}" @endif>
                            @if (! $expo->boundaryTarget())
                                {{ round($expo->elapsedMinutes() / 60, 1) }}h
                            @else
                                —
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ round($expo->elapsedMinutes() / 60, 1) }}h of {{ round($expo->totalMinutes() / 60) }}h elapsed
                        </p>
                    </div>
                </div>

                <div class="h-1.5 w-full bg-white/10">
                    <div class="h-full bg-indigo-500 transition-all" style="width: {{ $expo->progressPercent() }}%;"></div>
                </div>
            </div>
        @else
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                No expo schedule set. Add it under
                <a href="{{ route('admin.dashboard') }}" class="font-semibold underline">Dashboard → Expo Schedule</a>.
            </div>
        @endif

        {{-- Main menu -------------------------------------------------------}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($rangeOptions as $key => $opt)
                <a href="{{ route('admin.teacher-activity.index', ['range' => $key]) }}"
                   class="range-tab" aria-current="{{ $range['key'] === $key ? 'true' : 'false' }}">
                    <span class="flex items-center gap-2 text-sm font-semibold">
                        @if ($key === 'live')
                            <span class="viz-pulse"></span>
                        @endif
                        {{ $opt['label'] }}
                    </span>
                    <span class="range-hint text-xs {{ $range['key'] === $key ? '' : 'text-gray-400' }}">{{ $opt['hint'] }}</span>
                </a>
            @endforeach
        </div>

        @if ($isLiveRange)
            {{-- LIVE: one number, nothing else ------------------------------}}
            <div class="rounded-2xl bg-white p-10 text-center shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-center gap-2">
                    <span class="viz-pulse"></span>
                    <span class="text-sm font-medium text-gray-500">Teachers on the site right now</span>
                </div>
                <p id="live-count" class="mt-4 text-8xl font-semibold leading-none" style="color: var(--ink);">
                    {{ number_format($onlineNow) }}
                </p>
                <p class="mt-4 text-sm text-gray-400">
                    Active within the last {{ $onlineMinutes }} minutes ·
                    <span id="live-stamp">{{ $expo->now()->format('g:i:s A') }}</span>
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    Updates automatically every 15 seconds
                </p>
            </div>
        @else
            {{-- Metrics for the selected window -----------------------------}}
            @php
                $tiles = [];
                if ($range['showActive']) {
                    $tiles[] = ['label' => 'Teachers active', 'value' => $metrics['active'], 'note' => 'on the site during this window'];
                }
                $tiles[] = ['label' => 'Logins', 'value' => $metrics['logins'], 'note' => $metrics['loginTeachers'].' distinct '.\Illuminate\Support\Str::plural('teacher', $metrics['loginTeachers'])];
                $tiles[] = ['label' => 'New teacher signups', 'value' => $metrics['signups'], 'note' => 'registered in this window'];
                $tiles[] = ['label' => 'CV applications', 'value' => $metrics['applications'], 'note' => 'submitted to schools'];
            @endphp

            <div class="grid gap-4 sm:grid-cols-2 {{ count($tiles) === 4 ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">
                @foreach ($tiles as $tile)
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                        <p class="text-sm font-medium text-gray-500">{{ $tile['label'] }}</p>
                        <p class="mt-2 text-4xl font-semibold" style="color: var(--ink);">{{ number_format($tile['value']) }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ $tile['note'] }}</p>
                    </div>
                @endforeach
            </div>

            @if ($range['showAccountsShare'])
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <p class="text-sm font-medium text-gray-500">Never logged in</p>
                        <p class="text-sm text-gray-400">
                            <span class="font-semibold text-gray-900" style="font-variant-numeric: tabular-nums;">{{ number_format($accounts['neverLoggedIn']) }}</span>
                            of {{ number_format($accounts['total']) }} accounts
                        </p>
                    </div>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-gray-400" style="width: {{ max(1, $accounts['neverPercent']) }}%;"></div>
                        </div>
                        <span class="w-14 text-right text-xl font-semibold" style="color: var(--ink); font-variant-numeric: tabular-nums;">{{ $accounts['neverPercent'] }}%</span>
                    </div>
                    <p class="mt-2 text-xs text-gray-400">
                        {{ number_format($accounts['loggedInEver']) }} of {{ number_format($accounts['total']) }} registered teachers have signed in at least once.
                    </p>
                </div>
            @endif

            @if ($range['showCharts'])
                @if (empty($range['windows']))
                    <div class="rounded-xl bg-white p-10 text-center shadow-sm ring-1 ring-gray-100">
                        <p class="text-sm text-gray-500">
                            Today isn't an expo day. The expo runs {{ $expo->scheduleLabel() }}.
                        </p>
                    </div>
                @else
                    {{-- Activity over time: logins only (signups log in too) --}}
                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                        <h2 class="text-base font-semibold" style="color: var(--ink);">Activity over time</h2>
                        <p class="text-sm text-gray-500">
                            @php
                                $bm = $range['bucketMinutes'];
                                $bucketLabel = $bm < 60
                                    ? $bm.' minutes'
                                    : ($bm === 60 ? 'hour' : intdiv($bm, 60).' hours');
                            @endphp
                            Logins per {{ $bucketLabel }} · {{ $range['label'] }}
                            <span class="text-gray-400">— new signups are counted here too, since registering signs a teacher in.</span>
                        </p>
                        @include('admin.partials.activity-chart', [
                            'id' => 'chart-activity',
                            'points' => collect($series)->map(fn ($p) => ['label' => $p['label'], 'value' => $p['logins']])->all(),
                            'yTitle' => 'Logins',
                            'xTitle' => 'Local time ('.$expo->timezoneLabel().')',
                            'type' => 'area',
                            'accent' => 'var(--s1)',
                        ])
                    </div>

                    {{-- When teachers log in --}}
                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                        <h2 class="text-base font-semibold" style="color: var(--ink);">When teachers log in</h2>
                        <p class="text-sm text-gray-500">Hour of day, all expo days combined ({{ $expo->timezoneLabel() }})</p>
                        @include('admin.partials.activity-chart', [
                            'id' => 'chart-timeofday',
                            'points' => collect($timeOfDay)->map(fn ($p) => ['label' => $p['label'], 'value' => $p['total']])->all(),
                            'yTitle' => 'Logins',
                            'xTitle' => 'Hour of day (local)',
                            'type' => 'column',
                            'accent' => 'var(--s1)',
                        ])
                    </div>

                    @include('admin.partials.expo-highlight-card', ['highlights' => $highlights, 'expo' => $expo])

                    {{-- Most active teachers --}}
                    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <h2 class="text-base font-semibold" style="color: var(--ink);">Most active teachers</h2>
                            <p class="text-sm text-gray-500">Ranked by total logins, all time</p>
                        </div>
                        @if ($topTeachers->isEmpty())
                            <p class="px-6 py-8 text-center text-sm text-gray-400">No teachers registered yet.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                        <tr>
                                            <th class="px-6 py-3 font-medium">Teacher</th>
                                            <th class="px-6 py-3 font-medium">Status</th>
                                            <th class="px-6 py-3 font-medium">Logins</th>
                                            <th class="px-6 py-3 font-medium">Applications</th>
                                            <th class="px-6 py-3 font-medium">Last seen</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($topTeachers as $teacher)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-3">
                                                    <div class="flex items-center gap-2">
                                                        @if ($teacher->user?->isOnline($onlineMinutes))
                                                            <span class="h-2 w-2 shrink-0 rounded-full" style="background: var(--good);" title="Online now"></span>
                                                        @else
                                                            <span class="h-2 w-2 shrink-0 rounded-full bg-gray-200"></span>
                                                        @endif
                                                        <div class="min-w-0">
                                                            <p class="truncate font-medium text-gray-900">{{ $teacher->user?->name ?? '—' }}</p>
                                                            <p class="truncate text-xs text-gray-400">{{ $teacher->user?->email }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-3">
                                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $teacher->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                                        {{ ucfirst($teacher->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-3 text-gray-700" style="font-variant-numeric: tabular-nums;">{{ number_format($teacher->user?->login_count ?? 0) }}</td>
                                                <td class="px-6 py-3 text-gray-700" style="font-variant-numeric: tabular-nums;">{{ number_format($teacher->applications_count) }}</td>
                                                <td class="px-6 py-3 text-gray-500" title="{{ $expo->local($teacher->user?->last_seen_at)?->format('D j M, g:i A') }}">
                                                    {{ $teacher->user?->last_seen_at?->diffForHumans() ?? 'Never' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- Latest activity --}}
                    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                            <h2 class="text-base font-semibold" style="color: var(--ink);">Latest activity</h2>
                            <a href="{{ route('admin.activity.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">Full log →</a>
                        </div>
                        @if ($recent->isEmpty())
                            <p class="px-6 py-8 text-center text-sm text-gray-400">Nothing yet.</p>
                        @else
                            <ul class="divide-y divide-gray-100">
                                @foreach ($recent as $log)
                                    @php
                                        $dot = match (true) {
                                            str_contains($log->action, 'logged_in') => 'var(--s1)',
                                            str_contains($log->action, 'registered') => 'var(--s3)',
                                            str_contains($log->action, 'application') => 'var(--s2)',
                                            default => 'var(--muted)',
                                        };
                                    @endphp
                                    <li class="flex items-center justify-between gap-4 px-6 py-3 text-sm">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <span class="h-2 w-2 shrink-0 rounded-full" style="background: {{ $dot }};"></span>
                                            <span class="truncate text-gray-700">{{ $log->description }}</span>
                                        </div>
                                        <span class="shrink-0 text-xs text-gray-400" title="{{ $expo->local($log->created_at)?->format('D j M, g:i A') }}">
                                            {{ $expo->local($log->created_at)?->format('g:i A') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            @endif
        @endif
    </div>

    <script>
        (function () {
            // --- expo countdown, driven by a real instant so it stays correct
            const cd = document.querySelector('[data-expo-countdown]');
            if (cd) {
                const target = new Date(cd.dataset.expoCountdown).getTime();
                const tick = () => {
                    const diff = target - Date.now();
                    if (diff <= 0) { cd.textContent = '0m'; return; }
                    const d = Math.floor(diff / 86400000);
                    const h = Math.floor((diff % 86400000) / 3600000);
                    const m = Math.floor((diff % 3600000) / 60000);
                    cd.textContent = (d > 0 ? d + 'd ' : '') + (d > 0 || h > 0 ? h + 'h ' : '') + m + 'm';
                };
                tick();
                setInterval(tick, 20000);
            }

            // --- live counter polling (one indexed COUNT per hit)
            const liveCount = document.getElementById('live-count');
            if (liveCount) {
                const stamp = document.getElementById('live-stamp');
                const url = @json(route('admin.teacher-activity.live'));
                const poll = async () => {
                    if (document.hidden) return;
                    try {
                        const r = await fetch(url, { headers: { Accept: 'application/json' } });
                        if (!r.ok) return;
                        const d = await r.json();
                        liveCount.textContent = new Intl.NumberFormat().format(d.online);
                        if (stamp) stamp.textContent = d.at;
                    } catch (e) { /* transient network hiccup — keep last value */ }
                };
                setInterval(poll, 15000);
                document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
            }

            // --- shared chart hover layer
            const tipRow = (color, label, value) => {
                const line = document.createElement('div');
                line.style.cssText = 'display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:4px;';
                const left = document.createElement('span');
                left.style.cssText = 'display:flex;align-items:center;gap:6px;color:#52514e;';
                const key = document.createElement('span');
                key.style.cssText = 'display:inline-block;width:12px;height:3px;border-radius:2px;background:' + color + ';';
                left.appendChild(key);
                left.appendChild(document.createTextNode(label));
                const right = document.createElement('strong');
                right.style.cssText = 'color:#0b0b0b;font-variant-numeric:tabular-nums;';
                right.textContent = value;
                line.appendChild(left); line.appendChild(right);
                return line;
            };

            document.querySelectorAll('[data-chart-points]').forEach(host => {
                const points = JSON.parse(host.dataset.chartPoints || '[]');
                if (!points.length) return;
                const svg = host.querySelector('svg');
                const tip = host.querySelector('.viz-tip');
                const padL = Number(svg.dataset.padl), plotW = Number(svg.dataset.plotw);
                const n = points.length;

                svg.addEventListener('pointermove', evt => {
                    const rect = svg.getBoundingClientRect();
                    const vb = svg.viewBox.baseVal;
                    const x = ((evt.clientX - rect.left) / rect.width) * vb.width;
                    let idx = n <= 1 ? 0 : Math.round(((x - padL) / plotW) * (n - 1));
                    idx = Math.max(0, Math.min(n - 1, idx));
                    const p = points[idx];

                    tip.textContent = '';
                    const head = document.createElement('div');
                    head.style.cssText = 'font-weight:600;color:#0b0b0b;';
                    head.textContent = p.label;
                    tip.appendChild(head);
                    tip.appendChild(tipRow('#2a78d6', 'Logins', p.value));

                    const hx = evt.clientX - rect.left, hy = evt.clientY - rect.top;
                    let left = hx + 14;
                    if (left + tip.offsetWidth > host.clientWidth) left = hx - tip.offsetWidth - 14;
                    tip.style.left = Math.max(0, left) + 'px';
                    tip.style.top = Math.max(0, hy - 12) + 'px';
                    tip.dataset.show = '1';
                });
                svg.addEventListener('pointerleave', () => { tip.dataset.show = '0'; });
            });
        })();
    </script>
</x-admin-layout>
