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
        .range-tab {
            display: flex; flex-direction: column; gap: .25rem;
            border-radius: 1rem; padding: 1rem 1.15rem; text-align: left;
            border: 1px solid #e5e7eb; background: #fff; transition: all .16s ease;
        }
        .range-tab:hover { border-color: #c7d2fe; transform: translateY(-2px); box-shadow: 0 8px 20px -12px rgba(30,41,59,.35); }
        .range-tab[aria-current="true"] {
            background: #111827; border-color: #111827; color: #fff;
            box-shadow: 0 12px 24px -14px rgba(17,24,39,.8);
        }
        .range-tab[aria-current="true"] .range-hint { color: #9ca3af; }
        @media (prefers-reduced-motion: reduce) { .range-tab:hover { transform: none; } }
    </style>

    <div class="viz space-y-6">

        <div>
            <h1 class="font-display text-2xl font-semibold tracking-tight text-gray-900">Teacher Activity</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $range['description'] }}
                <span class="text-gray-400">· all times {{ $clock->timezone }} ({{ $clock->label() }})</span>
            </p>
        </div>

        {{-- Range menu ------------------------------------------------------}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($rangeOptions as $key => $opt)
                <a href="{{ route('admin.teacher-activity.index', ['range' => $key]) }}"
                   class="range-tab" aria-current="{{ $range['key'] === $key ? 'true' : 'false' }}">
                    <span class="text-sm font-semibold">{{ $opt['label'] }}</span>
                    <span class="range-hint text-xs {{ $range['key'] === $key ? '' : 'text-gray-400' }}">{{ $opt['hint'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Custom range picker --------------------------------------------}}
        @if ($range['key'] === 'custom')
            <form method="GET" action="{{ route('admin.teacher-activity.index') }}"
                  class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <input type="hidden" name="range" value="custom">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
                    <div>
                        <label for="from" class="block text-sm font-medium text-gray-700">From</label>
                        <input type="datetime-local" id="from" name="from" value="{{ $range['fromInput'] }}"
                               class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="to" class="block text-sm font-medium text-gray-700">To</label>
                        <input type="datetime-local" id="to" name="to" value="{{ $range['toInput'] }}"
                               class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <button type="submit"
                                class="w-full rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800">
                            Apply range
                        </button>
                    </div>
                    @php
                        $now = $clock->now();
                        $presets = [
                            'Last 24h' => [$now->copy()->subDay(), $now],
                            'Last 7 days' => [$now->copy()->subDays(7)->startOfDay(), $now],
                            'Last 30 days' => [$now->copy()->subDays(30)->startOfDay(), $now],
                            'This year' => [$now->copy()->startOfYear(), $now],
                        ];
                    @endphp
                    <div class="flex flex-wrap gap-2 text-xs">
                        @foreach ($presets as $name => $preset)
                            <a href="{{ route('admin.teacher-activity.index', ['range' => 'custom', 'from' => $preset[0]->format('Y-m-d\TH:i'), 'to' => $preset[1]->format('Y-m-d\TH:i')]) }}"
                               class="rounded-full bg-gray-100 px-3 py-1.5 font-medium text-gray-600 transition hover:bg-gray-200">{{ $name }}</a>
                        @endforeach
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-400">
                    Times are read in {{ $clock->timezone }} ({{ $clock->label() }}).
                </p>
            </form>

            @if ($range['notice'])
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm text-amber-800">
                    {{ $range['notice'] }}
                </div>
            @endif
        @endif

        {{-- Metrics ---------------------------------------------------------}}
        @php
            $tiles = [];
            if ($range['showActive']) {
                $tiles[] = ['label' => 'Teachers active', 'value' => $metrics['active'], 'note' => 'on the site during this window'];
            }
            $tiles[] = ['label' => 'Sign-ins', 'value' => $metrics['logins'], 'note' => $metrics['loginTeachers'].' distinct '.\Illuminate\Support\Str::plural('teacher', $metrics['loginTeachers'])];
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
            @php
                $bm = $range['bucketMinutes'];
                $bucketLabel = $bm < 60
                    ? $bm.' '.\Illuminate\Support\Str::plural('minute', $bm)
                    : ($bm < 1440
                        ? intdiv($bm, 60).' '.\Illuminate\Support\Str::plural('hour', intdiv($bm, 60))
                        : intdiv($bm, 1440).' '.\Illuminate\Support\Str::plural('day', intdiv($bm, 1440)));
            @endphp

            {{-- Activity over time: sign-ins only --}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-base font-semibold" style="color: var(--ink);">Activity over time</h2>
                <p class="text-sm text-gray-500">
                    Sign-ins per {{ $bucketLabel }}
                    <span class="text-gray-400">— new signups are counted here too, since registering signs a teacher in.</span>
                </p>
                @include('admin.partials.activity-chart', [
                    'id' => 'chart-activity',
                    'points' => collect($series)->map(fn ($p) => ['label' => $p['label'], 'value' => $p['logins']])->all(),
                    'yTitle' => 'Sign-ins',
                    'xTitle' => 'Local time ('.$clock->label().')',
                    'type' => 'area',
                    'accent' => 'var(--s1)',
                ])
            </div>

            {{-- When teachers log in --}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-base font-semibold" style="color: var(--ink);">When teachers log in</h2>
                <p class="text-sm text-gray-500">Hour of day across the selected range ({{ $clock->label() }})</p>
                @include('admin.partials.activity-chart', [
                    'id' => 'chart-timeofday',
                    'points' => collect($timeOfDay)->map(fn ($p) => ['label' => $p['label'], 'value' => $p['total']])->all(),
                    'yTitle' => 'Sign-ins',
                    'xTitle' => 'Hour of day (local)',
                    'type' => 'column',
                    'accent' => 'var(--s1)',
                ])
            </div>

            @include('admin.partials.expo-highlight-card', ['highlights' => $highlights])

            {{-- Most active teachers --}}
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-base font-semibold" style="color: var(--ink);">Most active teachers</h2>
                    <p class="text-sm text-gray-500">Ranked by total sign-ins, all time</p>
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
                                    <th class="px-6 py-3 font-medium">Sign-ins</th>
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
                                                    <span class="h-2 w-2 shrink-0 rounded-full" style="background: var(--good);" title="Active now"></span>
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
                                        <td class="px-6 py-3 text-gray-500" title="{{ $clock->local($teacher->user?->last_seen_at)?->format('D j M, g:i A') }}">
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
                                <span class="shrink-0 text-xs text-gray-400" title="{{ $clock->local($log->created_at)?->format('D j M Y, g:i A') }}">
                                    {{ $clock->local($log->created_at)?->format('j M, g:i A') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </div>

    <script>
        (function () {
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
                    tip.appendChild(tipRow('#2a78d6', 'Sign-ins', p.value));

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
