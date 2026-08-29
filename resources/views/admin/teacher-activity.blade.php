@php
    // ---- chart geometry helpers (presentation only) -------------------------
    /**
     * Pick an axis scale whose ticks land on round numbers: the smallest
     * "nice" step that covers the data in at most $maxTicks divisions.
     * Returns [top of scale, step, number of divisions].
     */
    $niceScale = function (int $v, int $maxTicks = 5): array {
        $v = max(1, $v);
        $steps = [1, 2, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000, 2000, 5000, 10000];

        foreach ($steps as $step) {
            $count = (int) ceil($v / $step);
            if ($count <= $maxTicks) {
                return [$step * $count, $step, $count];
            }
        }

        $step = end($steps);
        $count = (int) ceil($v / $step);

        return [$step * $count, $step, $count];
    };

    $roundedTopBar = function (float $x, float $y, float $w, float $h, float $baseline, float $r = 4): string {
        if ($h <= 0.5) {
            return '';
        }
        $r = min($r, $w / 2, $h);
        return sprintf(
            'M %.2f %.2f L %.2f %.2f Q %.2f %.2f %.2f %.2f L %.2f %.2f Q %.2f %.2f %.2f %.2f L %.2f %.2f Z',
            $x, $baseline,
            $x, $y + $r,
            $x, $y, $x + $r, $y,
            $x + $w - $r, $y,
            $x + $w, $y, $x + $w, $y + $r,
            $x + $w, $baseline
        );
    };

    // ---- line/area chart: daily logins vs unique teachers -------------------
    $n = max(1, count($series));
    $W = 720; $H = 260; $padL = 44; $padR = 12; $padT = 12; $padB = 32;
    $plotW = $W - $padL - $padR;
    $plotH = $H - $padT - $padB;
    $baseY = $padT + $plotH;

    [$maxVal, $yStep, $yTicks] = $niceScale((int) max(
        collect($series)->max('logins') ?? 0,
        collect($series)->max('teachers') ?? 0,
    ));

    $xAt = fn (int $i) => $n === 1 ? $padL + $plotW / 2 : $padL + ($i / ($n - 1)) * $plotW;
    $yAt = fn (float $v) => $padT + (1 - ($v / $maxVal)) * $plotH;

    $buildPath = function (string $key) use ($series, $xAt, $yAt) {
        $d = '';
        foreach ($series as $i => $row) {
            $d .= ($i === 0 ? 'M ' : ' L ').sprintf('%.2f %.2f', $xAt($i), $yAt($row[$key]));
        }
        return $d;
    };

    $loginsPath = $buildPath('logins');
    $teachersPath = $buildPath('teachers');
    $areaPath = $loginsPath !== ''
        ? $loginsPath.sprintf(' L %.2f %.2f L %.2f %.2f Z', $xAt($n - 1), $baseY, $xAt(0), $baseY)
        : '';

    // Label roughly six x-ticks regardless of range length.
    $tickEvery = max(1, (int) ceil($n / 6));

    // ---- column chart: logins by hour --------------------------------------
    $hW = 720; $hH = 260; $hPadL = 36; $hPadR = 12; $hPadT = 12; $hPadB = 28;
    $hPlotW = $hW - $hPadL - $hPadR;
    $hPlotH = $hH - $hPadT - $hPadB;
    $hBase = $hPadT + $hPlotH;
    [$hMax, $hStep, $hTicks] = $niceScale((int) (collect($hourly)->max('total') ?? 0), 3);
    $band = $hPlotW / 24;
    $barW = min(24, $band * 0.62);

    $totalTeachers = max(1, $live['total']);
    $bucketTotal = max(1, collect($buckets)->sum('count'));

    $delta = function (int $current, int $previous): array {
        $diff = $current - $previous;
        return [
            'diff' => $diff,
            'dir' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat'),
        ];
    };
@endphp

<x-admin-layout title="Teacher Activity">
    <style>
        .viz {
            --s1: #2a78d6;
            --s2: #eb6834;
            --ink: #0b0b0b;
            --ink-2: #52514e;
            --muted: #898781;
            --grid: #e1e0d9;
            --axis: #c3c2b7;
            --good: #006300;
            --bad: #d03b3b;
            --e-online: #0d366b;
            --e-today: #1c5cab;
            --e-week: #3987e5;
            --e-month: #86b6ef;
            --e-dormant: #898781;
            --e-never: #d4d2cb;
        }
        .viz-chart { width: 100%; height: auto; display: block; overflow: visible; }
        .viz-chart path, .viz-chart line, .viz-chart circle { vector-effect: non-scaling-stroke; }
        .viz-tip {
            position: absolute; pointer-events: none; opacity: 0; transition: opacity .12s;
            background: #ffffff; border: 1px solid rgba(11,11,11,.10);
            box-shadow: 0 6px 20px rgba(11,11,11,.10); border-radius: 10px;
            padding: 8px 10px; font-size: 12px; z-index: 20; min-width: 132px;
        }
        .viz-tip[data-show="1"] { opacity: 1; }
        .viz-seg { transition: filter .12s; }
        .viz-seg:hover { filter: brightness(1.12); }
        .viz-pulse {
            width: 8px; height: 8px; border-radius: 9999px; background: var(--good);
            box-shadow: 0 0 0 0 rgba(12,163,12,.55); animation: vizPulse 2s infinite;
        }
        @keyframes vizPulse {
            0%   { box-shadow: 0 0 0 0 rgba(12,163,12,.55); }
            70%  { box-shadow: 0 0 0 8px rgba(12,163,12,0); }
            100% { box-shadow: 0 0 0 0 rgba(12,163,12,0); }
        }
        @media (prefers-reduced-motion: reduce) { .viz-pulse { animation: none; } }
    </style>

    <div class="viz space-y-6">

        {{-- Filter row — scopes every time-based figure below it --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Range</span>
                <div class="inline-flex rounded-lg bg-gray-100 p-1">
                    @foreach ($ranges as $r)
                        <a href="{{ route('admin.teacher-activity.index', ['days' => $r]) }}"
                           class="rounded-md px-3 py-1.5 text-sm font-medium transition {{ $days === $r ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                            {{ $r }} days
                        </a>
                    @endforeach
                </div>
            </div>
            <p class="text-xs text-gray-400">
                Live counts are real-time · charts refresh at most once a minute
            </p>
        </div>

        {{-- Hero + live status --------------------------------------------------}}
        <div class="grid gap-4 lg:grid-cols-3">
            <div class="flex flex-col justify-center rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center gap-2">
                    <span class="viz-pulse"></span>
                    <span class="text-sm font-medium text-gray-500">Online now</span>
                </div>
                <p class="mt-3 text-6xl font-semibold leading-none" style="color: var(--ink);">{{ number_format($live['online']) }}</p>
                <p class="mt-3 text-sm text-gray-400">
                    teachers active in the last {{ $onlineMinutes }} minutes
                </p>

                @php
                    $onlinePct = $live['total'] > 0 ? round($live['online'] / $totalTeachers * 100) : 0;
                @endphp
                <div class="mt-5">
                    <div class="h-1.5 w-full overflow-hidden rounded-full" style="background: #dbeafe;">
                        <div class="h-full rounded-full" style="width: {{ max(2, $onlinePct) }}%; background: var(--s1);"></div>
                    </div>
                    <p class="mt-2 text-xs text-gray-400">
                        {{ $onlinePct }}% of all {{ number_format($live['total']) }} {{ \Illuminate\Support\Str::plural('teacher', $live['total']) }}
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:col-span-2">
                @php
                    $tiles = [
                        ['label' => 'Logged in today', 'value' => $live['today'], 'note' => 'of '.number_format($live['total']).' '.\Illuminate\Support\Str::plural('teacher', $live['total'])],
                        ['label' => 'Active this week', 'value' => $live['week'], 'note' => 'seen in the last 7 days'],
                        ['label' => 'Active accounts', 'value' => $live['activeAccounts'], 'note' => $live['suspended'].' suspended'],
                        ['label' => 'Never logged in', 'value' => $live['neverLoggedIn'], 'note' => 'registered but never signed in'],
                    ];
                @endphp
                @foreach ($tiles as $tile)
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                        <p class="text-sm font-medium text-gray-500">{{ $tile['label'] }}</p>
                        <p class="mt-2 text-3xl font-semibold" style="color: var(--ink);">{{ number_format($tile['value']) }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ $tile['note'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Period totals with change vs the preceding window -------------------}}
        <div class="grid gap-4 sm:grid-cols-3">
            @php
                $periodTiles = [
                    ['label' => 'Total logins', 'value' => $period['logins'], 'prev' => $period['previousLogins']],
                    ['label' => 'Teachers who logged in', 'value' => $period['teachers'], 'prev' => $period['previousTeachers']],
                    ['label' => 'CV applications', 'value' => $period['applications'], 'prev' => $period['previousApplications']],
                ];
            @endphp
            @foreach ($periodTiles as $tile)
                @php $d = $delta($tile['value'], $tile['prev']); @endphp
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium text-gray-500">{{ $tile['label'] }}</p>
                    <p class="mt-2 text-3xl font-semibold" style="color: var(--ink);">{{ number_format($tile['value']) }}</p>
                    <p class="mt-1 flex items-center gap-1 text-xs">
                        @if ($d['dir'] === 'flat')
                            <span class="text-gray-400">No change vs previous {{ $days }} days</span>
                        @else
                            <span class="font-semibold" style="color: {{ $d['dir'] === 'up' ? 'var(--good)' : 'var(--bad)' }};">
                                {{ $d['dir'] === 'up' ? '▲' : '▼' }} {{ number_format(abs($d['diff'])) }}
                            </span>
                            <span class="text-gray-400">vs previous {{ $days }} days</span>
                        @endif
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Chart 1 — daily logins vs unique teachers ---------------------------}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold" style="color: var(--ink);">Logins over time</h2>
                    <p class="text-sm text-gray-500">Daily sign-ins and how many distinct teachers they came from</p>
                </div>
                <div class="flex items-center gap-4 text-xs" style="color: var(--ink-2);">
                    <span class="flex items-center gap-1.5">
                        <span style="display:inline-block;width:14px;height:3px;border-radius:2px;background:var(--s1);"></span>
                        Logins
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span style="display:inline-block;width:14px;height:3px;border-radius:2px;background:var(--s2);"></span>
                        Unique teachers
                    </span>
                </div>
            </div>

            @if ($period['logins'] === 0)
                <p class="mt-8 mb-6 text-center text-sm text-gray-400">
                    No logins recorded in this range yet. Login history starts building from the moment this feature goes live.
                </p>
            @else
                <div class="relative mt-5" id="chart-daily">
                    <svg class="viz-chart" viewBox="0 0 {{ $W }} {{ $H }}" role="img" data-max="{{ $maxVal }}"
                         aria-label="Daily teacher logins and unique teachers over the last {{ $days }} days">
                        {{-- gridlines + y ticks --}}
                        @for ($t = 0; $t <= $yTicks; $t++)
                            @php
                                $v = $yStep * $t;
                                $gy = $yAt($v);
                            @endphp
                            <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $W - $padR }}" y2="{{ $gy }}"
                                  stroke="{{ $t === 0 ? 'var(--axis)' : 'var(--grid)' }}" stroke-width="1" />
                            <text x="{{ $padL - 8 }}" y="{{ $gy + 4 }}" text-anchor="end"
                                  font-size="11" fill="var(--muted)" style="font-variant-numeric: tabular-nums;">
                                {{ (int) round($v) }}
                            </text>
                        @endfor

                        {{-- x labels --}}
                        @foreach ($series as $i => $row)
                            @if ($i % $tickEvery === 0 || $i === $n - 1)
                                <text x="{{ $xAt($i) }}" y="{{ $H - 10 }}" text-anchor="middle"
                                      font-size="11" fill="var(--muted)">{{ $row['label'] }}</text>
                            @endif
                        @endforeach

                        {{-- area wash under the primary series --}}
                        <path d="{{ $areaPath }}" fill="var(--s1)" fill-opacity="0.10" />

                        <path d="{{ $teachersPath }}" fill="none" stroke="var(--s2)" stroke-width="2"
                              stroke-linejoin="round" stroke-linecap="round" />
                        <path d="{{ $loginsPath }}" fill="none" stroke="var(--s1)" stroke-width="2"
                              stroke-linejoin="round" stroke-linecap="round" />

                        {{-- crosshair + focus markers, driven by the hover layer --}}
                        <line id="daily-crosshair" x1="0" y1="{{ $padT }}" x2="0" y2="{{ $baseY }}"
                              stroke="var(--axis)" stroke-width="1" opacity="0" />
                        <circle id="daily-dot-1" r="4.5" fill="var(--s1)" stroke="#ffffff" stroke-width="2" opacity="0" />
                        <circle id="daily-dot-2" r="4.5" fill="var(--s2)" stroke="#ffffff" stroke-width="2" opacity="0" />

                        {{-- end-of-line direct labels --}}
                        <circle cx="{{ $xAt($n - 1) }}" cy="{{ $yAt($series[$n - 1]['logins']) }}" r="4"
                                fill="var(--s1)" stroke="#ffffff" stroke-width="2" />
                    </svg>
                    <div class="viz-tip" id="daily-tip"></div>
                </div>

                <details class="mt-4">
                    <summary class="cursor-pointer text-xs text-gray-500 hover:text-gray-900">View as table</summary>
                    <div class="mt-2 max-h-64 overflow-y-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="sticky top-0 bg-white text-gray-500">
                                <tr>
                                    <th class="py-1.5 pr-3 font-medium">Date</th>
                                    <th class="py-1.5 pr-3 font-medium">Logins</th>
                                    <th class="py-1.5 pr-3 font-medium">Unique teachers</th>
                                    <th class="py-1.5 font-medium">Applications</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700" style="font-variant-numeric: tabular-nums;">
                                @foreach (array_reverse($series) as $row)
                                    <tr class="border-t border-gray-100">
                                        <td class="py-1.5 pr-3">{{ $row['label'] }}</td>
                                        <td class="py-1.5 pr-3">{{ $row['logins'] }}</td>
                                        <td class="py-1.5 pr-3">{{ $row['teachers'] }}</td>
                                        <td class="py-1.5">{{ $row['applications'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Chart 2 — logins by hour ---------------------------------------}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-base font-semibold" style="color: var(--ink);">When teachers log in</h2>
                <p class="text-sm text-gray-500">Logins by hour of day, last {{ $days }} days</p>

                @if ($period['logins'] === 0)
                    <p class="mt-8 mb-6 text-center text-sm text-gray-400">No login data yet.</p>
                @else
                    <div class="relative mt-5" id="chart-hourly">
                        <svg class="viz-chart" viewBox="0 0 {{ $hW }} {{ $hH }}" role="img"
                             aria-label="Teacher logins by hour of day">
                            @for ($t = 0; $t <= $hTicks; $t++)
                                @php
                                    $v = $hStep * $t;
                                    $gy = $hPadT + (1 - ($v / $hMax)) * $hPlotH;
                                @endphp
                                <line x1="{{ $hPadL }}" y1="{{ $gy }}" x2="{{ $hW - $hPadR }}" y2="{{ $gy }}"
                                      stroke="{{ $t === 0 ? 'var(--axis)' : 'var(--grid)' }}" stroke-width="1" />
                                <text x="{{ $hPadL - 8 }}" y="{{ $gy + 4 }}" text-anchor="end"
                                      font-size="11" fill="var(--muted)" style="font-variant-numeric: tabular-nums;">
                                    {{ (int) round($v) }}
                                </text>
                            @endfor

                            @foreach ($hourly as $slot)
                                @php
                                    $bx = $hPadL + $slot['hour'] * $band + ($band - $barW) / 2;
                                    $bh = ($slot['total'] / $hMax) * $hPlotH;
                                    $by = $hBase - $bh;
                                @endphp
                                @if ($slot['total'] > 0)
                                    <path d="{{ $roundedTopBar($bx, $by, $barW, $bh, $hBase) }}" fill="var(--s1)"
                                          class="viz-seg" data-hour="{{ $slot['label'] }}" data-total="{{ $slot['total'] }}" />
                                @endif
                                {{-- transparent hit area so short/empty bars are still hoverable --}}
                                <rect x="{{ $hPadL + $slot['hour'] * $band }}" y="{{ $hPadT }}"
                                      width="{{ $band }}" height="{{ $hPlotH }}" fill="transparent"
                                      data-hour="{{ $slot['label'] }}" data-total="{{ $slot['total'] }}" />

                                @if ($slot['hour'] % 4 === 0)
                                    <text x="{{ $hPadL + $slot['hour'] * $band + $band / 2 }}" y="{{ $hH - 8 }}"
                                          text-anchor="middle" font-size="11" fill="var(--muted)">{{ $slot['label'] }}</text>
                                @endif
                            @endforeach
                        </svg>
                        <div class="viz-tip" id="hourly-tip"></div>
                    </div>

                    <details class="mt-4">
                        <summary class="cursor-pointer text-xs text-gray-500 hover:text-gray-900">View as table</summary>
                        <div class="mt-2 grid grid-cols-2 gap-x-6 text-xs text-gray-700 sm:grid-cols-3"
                             style="font-variant-numeric: tabular-nums;">
                            @foreach ($hourly as $slot)
                                <div class="flex justify-between border-t border-gray-100 py-1">
                                    <span class="text-gray-500">{{ $slot['label'] }}</span>
                                    <span>{{ $slot['total'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif
            </div>

            {{-- Chart 3 — engagement breakdown ---------------------------------}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-base font-semibold" style="color: var(--ink);">Engagement breakdown</h2>
                <p class="text-sm text-gray-500">Every teacher account, by how recently they were seen</p>

                <div class="mt-5 flex h-11 w-full gap-[2px] overflow-hidden rounded-lg">
                    @foreach ($buckets as $bucket)
                        @if ($bucket['count'] > 0)
                            <div class="viz-seg h-full first:rounded-l-lg last:rounded-r-lg"
                                 style="flex: {{ $bucket['count'] }} 1 0%; background: var(--e-{{ $bucket['key'] }});"
                                 title="{{ $bucket['label'] }}: {{ $bucket['count'] }} teacher{{ $bucket['count'] === 1 ? '' : 's' }}"></div>
                        @endif
                    @endforeach
                    @if ($bucketTotal === 0)
                        <div class="h-full w-full rounded-lg bg-gray-100"></div>
                    @endif
                </div>

                {{-- The legend carries every value, so nothing is gated behind hover --}}
                <ul class="mt-5 space-y-2 text-sm">
                    @foreach ($buckets as $bucket)
                        <li class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-2" style="color: var(--ink-2);">
                                <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:var(--e-{{ $bucket['key'] }});"></span>
                                {{ $bucket['label'] }}
                            </span>
                            <span class="flex items-baseline gap-2">
                                <span class="font-semibold" style="color: var(--ink); font-variant-numeric: tabular-nums;">{{ $bucket['count'] }}</span>
                                <span class="text-xs text-gray-400" style="font-variant-numeric: tabular-nums;">
                                    {{ $live['total'] > 0 ? round($bucket['count'] / $totalTeachers * 100) : 0 }}%
                                </span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Most active teachers ------------------------------------------------}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="text-base font-semibold" style="color: var(--ink);">Most active teachers</h2>
                <p class="text-sm text-gray-500">Ranked by total logins</p>
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
                                                <span class="h-2 w-2 shrink-0 rounded-full" style="background: var(--good);"
                                                      title="Online now"></span>
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
                                    <td class="px-6 py-3 text-gray-700" style="font-variant-numeric: tabular-nums;">
                                        {{ number_format($teacher->user?->login_count ?? 0) }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-700" style="font-variant-numeric: tabular-nums;">
                                        {{ number_format($teacher->applications_count) }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-500">
                                        {{ $teacher->user?->last_seen_at?->diffForHumans() ?? 'Never' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Recent activity ------------------------------------------------------}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="text-base font-semibold" style="color: var(--ink);">Recent teacher activity</h2>
            </div>
            @if ($recent->isEmpty())
                <p class="px-6 py-8 text-center text-sm text-gray-400">Nothing yet.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($recent as $log)
                        <li class="flex items-center justify-between gap-4 px-6 py-3 text-sm">
                            <div class="flex min-w-0 items-center gap-3">
                                @php
                                    $dotColor = match ($log->action) {
                                        'teacher.logged_in' => 'var(--s1)',
                                        'teacher.registered' => 'var(--good)',
                                        default => 'var(--s2)',
                                    };
                                @endphp
                                <span class="h-2 w-2 shrink-0 rounded-full" style="background: {{ $dotColor }};"></span>
                                <span class="truncate text-gray-700">{{ $log->description }}</span>
                            </div>
                            <span class="shrink-0 text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <script id="daily-data" type="application/json">@json($series)</script>

    <script>
        (function () {
            const svgPoint = (svg, evt) => {
                const rect = svg.getBoundingClientRect();
                const vb = svg.viewBox.baseVal;
                return {
                    x: ((evt.clientX - rect.left) / rect.width) * vb.width,
                    rectX: evt.clientX - rect.left,
                    rectY: evt.clientY - rect.top,
                };
            };

            const placeTip = (tip, host, x, y) => {
                const w = tip.offsetWidth;
                let left = x + 14;
                if (left + w > host.clientWidth) left = x - w - 14;
                tip.style.left = Math.max(0, left) + 'px';
                tip.style.top = Math.max(0, y - 12) + 'px';
                tip.dataset.show = '1';
            };

            const row = (color, label, value) => {
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
                line.appendChild(left);
                line.appendChild(right);
                return line;
            };

            // --- daily line chart: crosshair snaps to the nearest day ----------
            const dailyHost = document.getElementById('chart-daily');
            const dataEl = document.getElementById('daily-data');

            if (dailyHost && dataEl) {
                const data = JSON.parse(dataEl.textContent);
                const svg = dailyHost.querySelector('svg');
                const tip = document.getElementById('daily-tip');
                const cross = document.getElementById('daily-crosshair');
                const dot1 = document.getElementById('daily-dot-1');
                const dot2 = document.getElementById('daily-dot-2');
                const padL = 44, padT = 12, plotW = 664, plotH = 216;
                const n = data.length;
                const maxVal = Number(svg.dataset.max || 0) || (function () {
                    let m = 0;
                    data.forEach(d => { m = Math.max(m, d.logins, d.teachers); });
                    return m <= 4 ? 4 : m;
                })();

                const xAt = i => n === 1 ? padL + plotW / 2 : padL + (i / (n - 1)) * plotW;
                const yAt = v => padT + (1 - (v / maxVal)) * plotH;

                const show = evt => {
                    const p = svgPoint(svg, evt);
                    let idx = n === 1 ? 0 : Math.round(((p.x - padL) / plotW) * (n - 1));
                    idx = Math.max(0, Math.min(n - 1, idx));
                    const d = data[idx];

                    const cx = xAt(idx);
                    cross.setAttribute('x1', cx);
                    cross.setAttribute('x2', cx);
                    cross.setAttribute('opacity', '1');
                    dot1.setAttribute('cx', cx);
                    dot1.setAttribute('cy', yAt(d.logins));
                    dot1.setAttribute('opacity', '1');
                    dot2.setAttribute('cx', cx);
                    dot2.setAttribute('cy', yAt(d.teachers));
                    dot2.setAttribute('opacity', '1');

                    tip.textContent = '';
                    const head = document.createElement('div');
                    head.style.cssText = 'font-weight:600;color:#0b0b0b;';
                    head.textContent = d.label;
                    tip.appendChild(head);
                    tip.appendChild(row('#2a78d6', 'Logins', d.logins));
                    tip.appendChild(row('#eb6834', 'Teachers', d.teachers));
                    if (d.applications > 0) tip.appendChild(row('#898781', 'Applications', d.applications));

                    placeTip(tip, dailyHost, p.rectX, p.rectY);
                };

                const hide = () => {
                    tip.dataset.show = '0';
                    cross.setAttribute('opacity', '0');
                    dot1.setAttribute('opacity', '0');
                    dot2.setAttribute('opacity', '0');
                };

                svg.addEventListener('pointermove', show);
                svg.addEventListener('pointerleave', hide);
            }

            // --- hourly columns: each band is its own hit target ---------------
            const hourlyHost = document.getElementById('chart-hourly');

            if (hourlyHost) {
                const tip = document.getElementById('hourly-tip');
                hourlyHost.querySelectorAll('[data-hour]').forEach(el => {
                    el.addEventListener('pointermove', evt => {
                        const rect = hourlyHost.getBoundingClientRect();
                        tip.textContent = '';
                        const head = document.createElement('div');
                        head.style.cssText = 'font-weight:600;color:#0b0b0b;';
                        head.textContent = el.dataset.hour;
                        tip.appendChild(head);
                        tip.appendChild(row('#2a78d6', 'Logins', el.dataset.total));
                        placeTip(tip, hourlyHost, evt.clientX - rect.left, evt.clientY - rect.top);
                    });
                    el.addEventListener('pointerleave', () => { tip.dataset.show = '0'; });
                });
            }
        })();
    </script>
</x-admin-layout>
