@php
    /**
     * A single, consistent chart renderer used by every graph on this page so
     * the axes always look and behave the same way.
     *
     * $points   array of ['label' => string, 'value' => int]
     * $id       unique DOM id
     * $yTitle   y-axis title
     * $xTitle   x-axis title
     * $type     'area' | 'column'
     * $accent   css colour var
     */
    $points = $points ?? [];
    $type = $type ?? 'area';
    $accent = $accent ?? 'var(--s1)';
    $n = count($points);

    $W = 760; $H = 300; $padL = 54; $padR = 16; $padT = 22; $padB = 74;
    $plotW = $W - $padL - $padR;
    $plotH = $H - $padT - $padB;
    $baseY = $padT + $plotH;

    $rawMax = (int) (collect($points)->max('value') ?? 0);

    // Axis tops land on round numbers so every gridline reads cleanly.
    $steps = [1, 2, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000, 2000, 5000];
    $yStep = 1; $yTicks = 1;
    foreach ($steps as $candidate) {
        $count = (int) ceil(max(1, $rawMax) / $candidate);
        if ($count <= 5) { $yStep = $candidate; $yTicks = max(1, $count); break; }
    }
    $yMax = $yStep * $yTicks;

    $xAt = fn (int $i) => $n <= 1 ? $padL + $plotW / 2 : $padL + ($i / ($n - 1)) * $plotW;
    $yAt = fn (float $v) => $padT + (1 - ($v / $yMax)) * $plotH;

    // Label every tick when they fit, otherwise thin them out evenly.
    $labelEvery = max(1, (int) ceil($n / 18));
@endphp

<div class="relative mt-5" id="{{ $id }}" data-chart-points="{{ json_encode($points) }}">
    <svg class="viz-chart" viewBox="0 0 {{ $W }} {{ $H }}" role="img"
         data-padl="{{ $padL }}" data-padt="{{ $padT }}" data-plotw="{{ $plotW }}" data-ploth="{{ $plotH }}" data-max="{{ $yMax }}"
         aria-label="{{ $yTitle }} by {{ $xTitle }}">

        {{-- horizontal gridlines + y ticks --}}
        @for ($t = 0; $t <= $yTicks; $t++)
            @php $v = $yStep * $t; $gy = $yAt($v); @endphp
            <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $W - $padR }}" y2="{{ $gy }}"
                  stroke="{{ $t === 0 ? 'var(--axis)' : 'var(--grid)' }}" stroke-width="1" />
            <text x="{{ $padL - 10 }}" y="{{ $gy + 4 }}" text-anchor="end" font-size="11"
                  fill="var(--muted)" style="font-variant-numeric: tabular-nums;">{{ $v }}</text>
        @endfor

        {{-- y axis title --}}
        <text x="14" y="{{ $padT + $plotH / 2 }}" font-size="11" fill="var(--ink-2)" font-weight="600"
              text-anchor="middle" transform="rotate(-90 14 {{ $padT + $plotH / 2 }})">{{ $yTitle }}</text>

        @if ($n === 0)
            <text x="{{ $W / 2 }}" y="{{ $padT + $plotH / 2 }}" text-anchor="middle" font-size="12" fill="var(--muted)">
                No data in this window
            </text>
        @else
            @if ($type === 'area')
                @php
                    $line = '';
                    foreach ($points as $i => $p) {
                        $line .= ($i === 0 ? 'M ' : ' L ').sprintf('%.2f %.2f', $xAt($i), $yAt($p['value']));
                    }
                    $area = $n > 1
                        ? $line.sprintf(' L %.2f %.2f L %.2f %.2f Z', $xAt($n - 1), $baseY, $xAt(0), $baseY)
                        : '';
                @endphp
                @if ($area)
                    <path d="{{ $area }}" fill="{{ $accent }}" fill-opacity="0.10" />
                @endif
                <path d="{{ $line }}" fill="none" stroke="{{ $accent }}" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
                @if ($n <= 30)
                    @foreach ($points as $i => $p)
                        <circle cx="{{ $xAt($i) }}" cy="{{ $yAt($p['value']) }}" r="3.5" fill="{{ $accent }}" stroke="#ffffff" stroke-width="2" />
                    @endforeach
                @endif
            @else
                @php
                    $band = $n > 0 ? $plotW / $n : $plotW;
                    $barW = min(24, $band * 0.6);
                @endphp
                @foreach ($points as $i => $p)
                    @php
                        $bx = $padL + $i * $band + ($band - $barW) / 2;
                        $bh = $yMax > 0 ? ($p['value'] / $yMax) * $plotH : 0;
                        $by = $baseY - $bh;
                        $r = min(4, $barW / 2, max(0.01, $bh));
                    @endphp
                    @if ($p['value'] > 0 && $bh > 0.5)
                        <path d="M {{ $bx }} {{ $baseY }} L {{ $bx }} {{ $by + $r }} Q {{ $bx }} {{ $by }} {{ $bx + $r }} {{ $by }} L {{ $bx + $barW - $r }} {{ $by }} Q {{ $bx + $barW }} {{ $by }} {{ $bx + $barW }} {{ $by + $r }} L {{ $bx + $barW }} {{ $baseY }} Z"
                              fill="{{ $accent }}" class="viz-seg" />
                    @endif
                @endforeach
            @endif

            {{-- x ticks + rotated labels so every slot can be read --}}
            @foreach ($points as $i => $p)
                @php
                    $cx = $type === 'column' ? $padL + ($i + 0.5) * ($plotW / max(1, $n)) : $xAt($i);
                @endphp
                @if ($i % $labelEvery === 0 || $i === $n - 1)
                    <line x1="{{ $cx }}" y1="{{ $baseY }}" x2="{{ $cx }}" y2="{{ $baseY + 4 }}" stroke="var(--axis)" stroke-width="1" />
                    <text x="{{ $cx }}" y="{{ $baseY + 8 }}" font-size="10" fill="var(--muted)"
                          text-anchor="end" transform="rotate(-45 {{ $cx }} {{ $baseY + 8 }})">{{ $p['label'] }}</text>
                @endif
            @endforeach
        @endif

        {{-- x axis title --}}
        <text x="{{ $padL + $plotW / 2 }}" y="{{ $H - 4 }}" text-anchor="middle" font-size="11"
              fill="var(--ink-2)" font-weight="600">{{ $xTitle }}</text>
    </svg>
    <div class="viz-tip" id="{{ $id }}-tip"></div>
</div>

<details class="mt-3">
    <summary class="cursor-pointer text-xs text-gray-500 hover:text-gray-900">View as table</summary>
    <div class="mt-2 max-h-56 overflow-y-auto">
        <table class="w-full text-left text-xs">
            <thead class="sticky top-0 bg-white text-gray-500">
                <tr>
                    <th class="py-1.5 pr-3 font-medium">{{ $xTitle }}</th>
                    <th class="py-1.5 font-medium">{{ $yTitle }}</th>
                </tr>
            </thead>
            <tbody class="text-gray-700" style="font-variant-numeric: tabular-nums;">
                @foreach ($points as $p)
                    <tr class="border-t border-gray-100">
                        <td class="py-1.5 pr-3">{{ $p['label'] }}</td>
                        <td class="py-1.5">{{ $p['value'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</details>
