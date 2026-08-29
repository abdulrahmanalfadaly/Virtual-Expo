@php
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

    // ---- main time-series chart --------------------------------------------
    $n = count($series);
    $W = 720; $H = 260; $padL = 44; $padR = 12; $padT = 20; $padB = 32;
    $plotW = $W - $padL - $padR;
    $plotH = $H - $padT - $padB;
    $baseY = $padT + $plotH;

    [$maxVal, $yStep, $yTicks] = $niceScale((int) max(
        collect($series)->max('logins') ?? 0,
        collect($series)->max('teachers') ?? 0,
        collect($series)->max('signups') ?? 0,
    ));

    $xAt = fn (int $i) => $n <= 1 ? $padL + $plotW / 2 : $padL + ($i / ($n - 1)) * $plotW;
    $yAt = fn (float $v) => $padT + (1 - ($v / $maxVal)) * $plotH;

    $buildPath = function (string $key) use ($series, $xAt, $yAt) {
        $d = '';
        foreach ($series as $i => $row) {
            $d .= ($i === 0 ? 'M ' : ' L ').sprintf('%.2f %.2f', $xAt($i), $yAt($row[$key]));
        }
        return $d;
    };

    $loginsPath = $n ? $buildPath('logins') : '';
    $teachersPath = $n ? $buildPath('teachers') : '';
    $signupsPath = $n ? $buildPath('signups') : '';
    $areaPath = $n > 1
        ? $loginsPath.sprintf(' L %.2f %.2f L %.2f %.2f Z', $xAt($n - 1), $baseY, $xAt(0), $baseY)
        : '';

    // With only a handful of buckets a polyline reads as almost nothing, so
    // the points themselves carry the data instead.
    $showPoints = $n > 0 && $n <= 14;
    $tickEvery = max(1, (int) ceil($n / 8));

    // Expo-day boundaries: buckets are hourly and start exactly at the expo
    // start, so every 24th bucket opens a new day.
    $dayMarkers = [];
    if (($range['key'] ?? null) === 'expo' && ($range['granularity'] ?? '') === 'hour') {
        for ($i = 0; $i < $n; $i += 24) {
            $dayMarkers[] = ['i' => $i, 'day' => intdiv($i, 24) + 1];
        }
    }

    // ---- hour-of-day column chart ------------------------------------------
    $hW = 720; $hH = 260; $hPadL = 36; $hPadR = 12; $hPadT = 12; $hPadB = 28;
    $hPlotW = $hW - $hPadL - $hPadR;
    $hPlotH = $hH - $hPadT - $hPadB;
    $hBase = $hPadT + $hPlotH;
    [$hMax, $hStep, $hTicks] = $niceScale((int) (collect($hourly)->max('total') ?? 0), 3);
    $band = $hPlotW / 24;
    $barW = min(24, $band * 0.62);

    $totalTeachers = max(1, $live['total']);
    $maxAction = max(1, (int) (collect($actionBreakdown)->max('total') ?? 1));
    $hasSeriesData = $totals['logins'] > 0 || $totals['signups'] > 0 || $totals['applications'] > 0;
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
            padding: 8px 10px; font-size: 12px; z-index: 20; min-width: 140px;
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

        {{-- Expo status banner — the frame every number below is read against --}}
        @if ($expo->isConfigured())
            <div class="overflow-hidden rounded-xl bg-gray-900 text-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-5">
                    <div>
                        <div class="flex items-center gap-2">
                            @if ($expo->isRunning())
                                <span class="viz-pulse"></span>
                            @endif
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ $expo->statusLabel() }}
                            </span>
                        </div>
                        <p class="mt-1 text-2xl font-semibold">
                            @if ($expo->hasStarted() && ! $expo->hasEnded())
                                Day {{ $expo->currentDay() }} of {{ $expo->days }}
                            @elseif ($expo->hasEnded())
                                Expo finished
                            @else
                                Expo not started
                            @endif
                        </p>
                        <p class="mt-1 text-sm text-gray-400">
                            {{ $expo->start->format('D j M, g:i a') }} → {{ $expo->end()->format('D j M, g:i a') }}
                            <span class="text-gray-500">(server time)</span>
                        </p>
                    </div>

                    <div class="text-right">
                        @if ($expo->isRunning())
                            <p class="text-xs uppercase tracking-wider text-gray-400">Time remaining</p>
                            <p class="mt-1 text-2xl font-semibold" data-expo-countdown="{{ $expo->end()->toIso8601String() }}">
                                —
                            </p>
                            <p class="mt-1 text-xs text-gray-500">{{ $expo->hoursElapsed() }}h elapsed</p>
                        @elseif (! $expo->hasStarted())
                            <p class="text-xs uppercase tracking-wider text-gray-400">Opens in</p>
                            <p class="mt-1 text-2xl font-semibold" data-expo-countdown="{{ $expo->start->toIso8601String() }}">—</p>
                        @endif
                    </div>
                </div>

                <div class="h-1.5 w-full bg-white/10">
                    <div class="h-full bg-indigo-500 transition-all" style="width: {{ $expo->progressPercent() }}%;"></div>
                </div>
            </div>
        @else
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                No expo dates set yet. Add them under
                <a href="{{ route('admin.dashboard') }}" class="font-semibold underline">Dashboard → Expo Schedule</a>
                so activity can be reported against the event window.
            </div>
        @endif

        {{-- Filter row — scopes every time-based figure below it --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Showing</span>
                <div class="inline-flex rounded-lg bg-gray-100 p-1">
                    @foreach ($rangeOptions as $key => $label)
                        <a href="{{ route('admin.teacher-activity.index', ['range' => $key]) }}"
                           class="rounded-md px-3 py-1.5 text-sm font-medium transition {{ $range['key'] === $key ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
            <p class="text-xs text-gray-400">
                {{ $range['since']->format('j M, g:i a') }} → {{ $range['until']->format('j M, g:i a') }}
            </p>
        </div>

        {{-- Live status ------------------------------------------------------}}
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

                @php $onlinePct = $live['total'] > 0 ? round($live['online'] / $totalTeachers * 100) : 0; @endphp
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
                        ['label' => 'Logins in the last hour', 'value' => $live['lastHourLogins'], 'note' => 'across all teachers'],
                        ['label' => 'Logged in today', 'value' => $live['today'], 'note' => 'of '.number_format($live['total']).' '.\Illuminate\Support\Str::plural('teacher', $live['total'])],
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

        {{-- Totals for the selected window ------------------------------------}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $rangeTiles = [
                    ['label' => 'Total logins', 'value' => $totals['logins']],
                    ['label' => 'Teachers who logged in', 'value' => $totals['teachers']],
                    ['label' => 'New teacher signups', 'value' => $totals['signups']],
                    ['label' => 'CV applications', 'value' => $totals['applications']],
                ];
            @endphp
            @foreach ($rangeTiles as $tile)
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium text-gray-500">{{ $tile['label'] }}</p>
                    <p class="mt-2 text-3xl font-semibold" style="color: var(--ink);">{{ number_format($tile['value']) }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $range['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Main time series ---------------------------------------------------}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold" style="color: var(--ink);">Activity over time</h2>
                    <p class="text-sm text-gray-500">
                        {{ $range['granularity'] === 'hour' ? 'Hour by hour' : 'Day by day' }} · {{ $range['label'] }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-xs" style="color: var(--ink-2);">
                    @foreach ([['Logins', 'var(--s1)'], ['Unique teachers', 'var(--s2)'], ['New signups', 'var(--s3)']] as [$name, $col])
                        <span class="flex items-center gap-1.5">
                            <span style="display:inline-block;width:14px;height:3px;border-radius:2px;background:{{ $col }};"></span>
                            {{ $name }}
                        </span>
                    @endforeach
                </div>
            </div>

            @if (! $hasSeriesData)
                <p class="mt-8 mb-6 text-center text-sm text-gray-400">
                    Nothing recorded in this window yet.
                    @if ($range['key'] === 'expo')
                        The expo has been open for {{ $expo->hoursElapsed() }}h — activity will appear here as teachers arrive.
                    @endif
                </p>
            @else
                <div class="relative mt-5" id="chart-daily">
                    <svg class="viz-chart" viewBox="0 0 {{ $W }} {{ $H }}" role="img" data-max="{{ $maxVal }}"
                         data-padl="{{ $padL }}" data-padt="{{ $padT }}" data-plotw="{{ $plotW }}" data-ploth="{{ $plotH }}"
                         aria-label="Teacher logins, unique teachers and signups over {{ $range['label'] }}">
                        @for ($t = 0; $t <= $yTicks; $t++)
                            @php $v = $yStep * $t; $gy = $yAt($v); @endphp
                            <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $W - $padR }}" y2="{{ $gy }}"
                                  stroke="{{ $t === 0 ? 'var(--axis)' : 'var(--grid)' }}" stroke-width="1" />
                            <text x="{{ $padL - 8 }}" y="{{ $gy + 4 }}" text-anchor="end"
                                  font-size="11" fill="var(--muted)" style="font-variant-numeric: tabular-nums;">{{ (int) round($v) }}</text>
                        @endfor

                        {{-- expo day dividers --}}
                        @foreach ($dayMarkers as $marker)
                            @if ($marker['i'] > 0)
                                <line x1="{{ $xAt($marker['i']) }}" y1="{{ $padT }}" x2="{{ $xAt($marker['i']) }}" y2="{{ $baseY }}"
                                      stroke="var(--axis)" stroke-width="1" stroke-dasharray="0" opacity="0.55" />
                            @endif
                            <text x="{{ $xAt($marker['i']) + 4 }}" y="{{ $padT - 7 }}" font-size="10"
                                  fill="var(--muted)" font-weight="600">Day {{ $marker['day'] }}</text>
                        @endforeach

                        @foreach ($series as $i => $row)
                            @if ($i % $tickEvery === 0 || $i === $n - 1)
                                <text x="{{ $xAt($i) }}" y="{{ $H - 10 }}" text-anchor="middle"
                                      font-size="11" fill="var(--muted)">{{ $row['short'] }}</text>
                            @endif
                        @endforeach

                        @if ($areaPath)
                            <path d="{{ $areaPath }}" fill="var(--s1)" fill-opacity="0.10" />
                        @endif

                        <path d="{{ $signupsPath }}" fill="none" stroke="var(--s3)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
                        <path d="{{ $teachersPath }}" fill="none" stroke="var(--s2)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
                        <path d="{{ $loginsPath }}" fill="none" stroke="var(--s1)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />

                        @if ($showPoints)
                            @foreach ($series as $i => $row)
                                <circle cx="{{ $xAt($i) }}" cy="{{ $yAt($row['signups']) }}" r="4" fill="var(--s3)" stroke="#ffffff" stroke-width="2" />
                                <circle cx="{{ $xAt($i) }}" cy="{{ $yAt($row['teachers']) }}" r="4" fill="var(--s2)" stroke="#ffffff" stroke-width="2" />
                                <circle cx="{{ $xAt($i) }}" cy="{{ $yAt($row['logins']) }}" r="4" fill="var(--s1)" stroke="#ffffff" stroke-width="2" />
                            @endforeach
                        @endif

                        <line id="daily-crosshair" x1="0" y1="{{ $padT }}" x2="0" y2="{{ $baseY }}" stroke="var(--axis)" stroke-width="1" opacity="0" />
                        <circle id="daily-dot-1" r="4.5" fill="var(--s1)" stroke="#ffffff" stroke-width="2" opacity="0" />
                        <circle id="daily-dot-2" r="4.5" fill="var(--s2)" stroke="#ffffff" stroke-width="2" opacity="0" />
                        <circle id="daily-dot-3" r="4.5" fill="var(--s3)" stroke="#ffffff" stroke-width="2" opacity="0" />
                    </svg>
                    <div class="viz-tip" id="daily-tip"></div>
                </div>

                <details class="mt-4">
                    <summary class="cursor-pointer text-xs text-gray-500 hover:text-gray-900">View as table</summary>
                    <div class="mt-2 max-h-64 overflow-y-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="sticky top-0 bg-white text-gray-500">
                                <tr>
                                    <th class="py-1.5 pr-3 font-medium">When</th>
                                    <th class="py-1.5 pr-3 font-medium">Logins</th>
                                    <th class="py-1.5 pr-3 font-medium">Unique teachers</th>
                                    <th class="py-1.5 pr-3 font-medium">Signups</th>
                                    <th class="py-1.5 font-medium">Applications</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700" style="font-variant-numeric: tabular-nums;">
                                @foreach (array_reverse($series) as $row)
                                    <tr class="border-t border-gray-100">
                                        <td class="py-1.5 pr-3">{{ $row['label'] }}</td>
                                        <td class="py-1.5 pr-3">{{ $row['logins'] }}</td>
                                        <td class="py-1.5 pr-3">{{ $row['teachers'] }}</td>
                                        <td class="py-1.5 pr-3">{{ $row['signups'] }}</td>
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
            {{-- Hour-of-day --------------------------------------------------}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-base font-semibold" style="color: var(--ink);">When teachers log in</h2>
                <p class="text-sm text-gray-500">Logins by hour of day · {{ $range['label'] }}</p>

                @if ($totals['logins'] === 0)
                    <p class="mt-8 mb-6 text-center text-sm text-gray-400">No logins in this window yet.</p>
                @else
                    <div class="relative mt-5" id="chart-hourly">
                        <svg class="viz-chart" viewBox="0 0 {{ $hW }} {{ $hH }}" role="img" aria-label="Teacher logins by hour of day">
                            @for ($t = 0; $t <= $hTicks; $t++)
                                @php $v = $hStep * $t; $gy = $hPadT + (1 - ($v / $hMax)) * $hPlotH; @endphp
                                <line x1="{{ $hPadL }}" y1="{{ $gy }}" x2="{{ $hW - $hPadR }}" y2="{{ $gy }}"
                                      stroke="{{ $t === 0 ? 'var(--axis)' : 'var(--grid)' }}" stroke-width="1" />
                                <text x="{{ $hPadL - 8 }}" y="{{ $gy + 4 }}" text-anchor="end"
                                      font-size="11" fill="var(--muted)" style="font-variant-numeric: tabular-nums;">{{ (int) round($v) }}</text>
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

            {{-- Engagement breakdown ------------------------------------------}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-base font-semibold" style="color: var(--ink);">Engagement breakdown</h2>
                <p class="text-sm text-gray-500">Every teacher account, by how recently they were seen</p>

                <div class="mt-5 flex h-11 w-full gap-[2px] overflow-hidden rounded-lg">
                    @foreach ($buckets as $bucket)
                        @if ($bucket['count'] > 0)
                            <div class="viz-seg h-full first:rounded-l-lg last:rounded-r-lg"
                                 style="flex: {{ $bucket['count'] }} 1 0%; background: var(--e-{{ $bucket['key'] }});"
                                 title="{{ $bucket['label'] }}: {{ $bucket['count'] }} {{ \Illuminate\Support\Str::plural('teacher', $bucket['count']) }}"></div>
                        @endif
                    @endforeach
                </div>

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

        {{-- Every recorded action type in the window ---------------------------}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold" style="color: var(--ink);">All recorded activity</h2>
                    <p class="text-sm text-gray-500">Every action type logged in this window — {{ $range['label'] }}</p>
                </div>
                @if ($preExpo && $range['key'] === 'expo')
                    <a href="{{ route('admin.teacher-activity.index', ['range' => 'all']) }}"
                       class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-200">
                        {{ number_format($preExpo['logs']) }} earlier entries from before the expo → view all time
                    </a>
                @endif
            </div>

            @if (empty($actionBreakdown))
                <p class="mt-8 mb-4 text-center text-sm text-gray-400">No activity recorded in this window.</p>
            @else
                <ul class="mt-5 space-y-3">
                    @foreach ($actionBreakdown as $item)
                        <li>
                            <div class="flex items-center justify-between gap-4 text-sm">
                                <span class="truncate" style="color: var(--ink-2);">{{ $item['label'] }}</span>
                                <span class="flex shrink-0 items-baseline gap-3">
                                    <span class="text-xs text-gray-400">{{ $item['latest']->diffForHumans() }}</span>
                                    <span class="font-semibold" style="color: var(--ink); font-variant-numeric: tabular-nums;">{{ number_format($item['total']) }}</span>
                                </span>
                            </div>
                            <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full" style="width: {{ max(1.5, $item['total'] / $maxAction * 100) }}%; background: var(--s1);"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($preExpo)
                <p class="mt-6 border-t border-gray-100 pt-4 text-xs text-gray-400">
                    Site history begins {{ $preExpo['firstAt']->format('j M Y') }} —
                    {{ number_format($preExpo['logs']) }} entries, {{ $preExpo['teachers'] }} {{ \Illuminate\Support\Str::plural('teacher', $preExpo['teachers']) }}
                    and {{ $preExpo['schools'] }} {{ \Illuminate\Support\Str::plural('school', $preExpo['schools']) }} were already registered before the expo opened.
                </p>
            @endif
        </div>

        {{-- Most active teachers ------------------------------------------------}}
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

        {{-- Recent activity — unfiltered, so setup-era entries stay visible ------}}
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
                            $dotColor = match (true) {
                                str_contains($log->action, 'logged_in') => 'var(--s1)',
                                str_contains($log->action, 'registered') => 'var(--s3)',
                                str_contains($log->action, 'application') => 'var(--s2)',
                                default => 'var(--muted)',
                            };
                            $isPreExpo = $expo->isConfigured() && $log->created_at->lessThan($expo->start);
                        @endphp
                        <li class="flex items-center justify-between gap-4 px-6 py-3 text-sm">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="h-2 w-2 shrink-0 rounded-full" style="background: {{ $dotColor }};"></span>
                                <span class="truncate text-gray-700">{{ $log->description }}</span>
                                @if ($isPreExpo)
                                    <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500">pre-expo</span>
                                @endif
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
            // --- live expo countdown -------------------------------------------
            const cd = document.querySelector('[data-expo-countdown]');
            if (cd) {
                const target = new Date(cd.dataset.expoCountdown).getTime();
                const tick = () => {
                    const diff = target - Date.now();
                    if (diff <= 0) { cd.textContent = '0h 0m'; return; }
                    const d = Math.floor(diff / 86400000);
                    const h = Math.floor((diff % 86400000) / 3600000);
                    const m = Math.floor((diff % 3600000) / 60000);
                    cd.textContent = (d > 0 ? d + 'd ' : '') + h + 'h ' + m + 'm';
                };
                tick();
                setInterval(tick, 30000);
            }

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

            // --- main chart: crosshair snaps to the nearest bucket ---------------
            const dailyHost = document.getElementById('chart-daily');
            const dataEl = document.getElementById('daily-data');

            if (dailyHost && dataEl) {
                const data = JSON.parse(dataEl.textContent);
                const svg = dailyHost.querySelector('svg');
                const tip = document.getElementById('daily-tip');
                const cross = document.getElementById('daily-crosshair');
                const dots = [
                    document.getElementById('daily-dot-1'),
                    document.getElementById('daily-dot-2'),
                    document.getElementById('daily-dot-3'),
                ];
                const padL = Number(svg.dataset.padl);
                const padT = Number(svg.dataset.padt);
                const plotW = Number(svg.dataset.plotw);
                const plotH = Number(svg.dataset.ploth);
                const maxVal = Number(svg.dataset.max) || 1;
                const n = data.length;

                const xAt = i => n <= 1 ? padL + plotW / 2 : padL + (i / (n - 1)) * plotW;
                const yAt = v => padT + (1 - (v / maxVal)) * plotH;

                svg.addEventListener('pointermove', evt => {
                    const p = svgPoint(svg, evt);
                    let idx = n <= 1 ? 0 : Math.round(((p.x - padL) / plotW) * (n - 1));
                    idx = Math.max(0, Math.min(n - 1, idx));
                    const d = data[idx];
                    const cx = xAt(idx);

                    cross.setAttribute('x1', cx);
                    cross.setAttribute('x2', cx);
                    cross.setAttribute('opacity', '1');
                    [d.logins, d.teachers, d.signups].forEach((v, k) => {
                        dots[k].setAttribute('cx', cx);
                        dots[k].setAttribute('cy', yAt(v));
                        dots[k].setAttribute('opacity', '1');
                    });

                    tip.textContent = '';
                    const head = document.createElement('div');
                    head.style.cssText = 'font-weight:600;color:#0b0b0b;';
                    head.textContent = d.label;
                    tip.appendChild(head);
                    tip.appendChild(row('#2a78d6', 'Logins', d.logins));
                    tip.appendChild(row('#eb6834', 'Teachers', d.teachers));
                    tip.appendChild(row('#1baf7a', 'Signups', d.signups));
                    if (d.applications > 0) tip.appendChild(row('#898781', 'Applications', d.applications));

                    placeTip(tip, dailyHost, p.rectX, p.rectY);
                });

                svg.addEventListener('pointerleave', () => {
                    tip.dataset.show = '0';
                    cross.setAttribute('opacity', '0');
                    dots.forEach(d => d.setAttribute('opacity', '0'));
                });
            }

            // --- hour-of-day columns -------------------------------------------
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
