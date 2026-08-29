@php
    /**
     * A share-ready recap card. Every figure is a real count — nothing here is
     * projected, padded or rounded up. Fixed 1200x630 so it exports straight to
     * a social-friendly PNG.
     */
    $h = $highlights;
    $siteName = \App\Models\SiteSetting::getLocalized('site_name', 'Virtual School Expo');

    // Inline the logo so the exported PNG is self-contained. Skipped when the
    // file is large, since it would bloat the data URI for no visual gain.
    $logoData = null;
    $logoPath = \App\Models\SiteSetting::get('expo_logo_path');
    if ($logoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath)) {
        $size = \Illuminate\Support\Facades\Storage::disk('public')->size($logoPath);
        if ($size && $size < 300000) {
            $mime = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($logoPath) ?: 'image/png';
            $logoData = 'data:'.$mime.';base64,'.base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($logoPath));
        }
    }

    $W = 1200; $H = 630;

    $stats = [
        ['value' => $h['applications'], 'label' => 'CVs submitted', 'accent' => '#34d399'],
        ['value' => $h['schools'],      'label' => 'Schools exhibiting', 'accent' => '#60a5fa'],
        ['value' => $h['logins'],       'label' => 'Sign-ins', 'accent' => '#fbbf24'],
        ['value' => $h['hoursLive'],    'label' => 'Hours live', 'accent' => '#f472b6', 'suffix' => 'h'],
    ];

    // Hero: the headline reach figure, with a sensible fallback while the
    // expo is still young and nobody has signed in yet.
    $heroValue = $h['activeTeachers'] > 0 ? $h['activeTeachers'] : $h['teachers'];
    $heroLabel = $h['activeTeachers'] > 0 ? 'teachers took part' : 'teachers registered';

    $series = $h['series'] ?? [];
    $seriesMax = max(1, (int) (collect($series)->max() ?? 0));
    $hasCurve = count($series) > 1 && array_sum($series) > 0;

    $cx = 90; $cy = 470; $cw = $W - 180; $ch = 96;
    $curvePath = '';
    $curveArea = '';
    if ($hasCurve) {
        $count = count($series);
        foreach ($series as $i => $v) {
            $x = $cx + ($count === 1 ? $cw / 2 : ($i / ($count - 1)) * $cw);
            $y = $cy + $ch - ($v / $seriesMax) * $ch;
            $curvePath .= ($i === 0 ? 'M ' : ' L ').sprintf('%.1f %.1f', $x, $y);
        }
        $curveArea = $curvePath.sprintf(' L %.1f %.1f L %.1f %.1f Z', $cx + $cw, $cy + $ch, $cx, $cy + $ch);
    }
@endphp

<div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold" style="color: var(--ink);">Expo highlights</h2>
            <p class="text-sm text-gray-500">A share-ready recap card — every number is a real count.</p>
        </div>
        <button type="button" id="highlight-download"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Download PNG
        </button>
    </div>

    <div class="mt-5 overflow-hidden rounded-xl ring-1 ring-gray-200">
        <svg id="highlight-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {{ $W }} {{ $H }}"
             width="{{ $W }}" height="{{ $H }}" style="width:100%;height:auto;display:block;"
             font-family="system-ui, -apple-system, 'Segoe UI', sans-serif"
             role="img" aria-label="Expo highlights summary card">

            <defs>
                <linearGradient id="hcBg" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#0f172a"/>
                    <stop offset="55%" stop-color="#1e1b4b"/>
                    <stop offset="100%" stop-color="#312e81"/>
                </linearGradient>
                <linearGradient id="hcCurve" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#818cf8" stop-opacity="0.55"/>
                    <stop offset="100%" stop-color="#818cf8" stop-opacity="0"/>
                </linearGradient>
                <radialGradient id="hcGlow" cx="0.5" cy="0.5" r="0.5">
                    <stop offset="0%" stop-color="#6366f1" stop-opacity="0.5"/>
                    <stop offset="100%" stop-color="#6366f1" stop-opacity="0"/>
                </radialGradient>
            </defs>

            <rect width="{{ $W }}" height="{{ $H }}" fill="url(#hcBg)"/>
            <ellipse cx="1020" cy="120" rx="380" ry="260" fill="url(#hcGlow)"/>
            <ellipse cx="120" cy="580" rx="300" ry="200" fill="url(#hcGlow)" opacity="0.6"/>

            {{-- Header --}}
            @if ($logoData)
                <image href="{{ $logoData }}" x="88" y="52" height="56" width="56" preserveAspectRatio="xMidYMid meet"/>
                <text x="160" y="90" fill="#ffffff" font-size="26" font-weight="700">{{ $siteName }}</text>
            @else
                <text x="88" y="90" fill="#ffffff" font-size="28" font-weight="700">{{ $siteName }}</text>
            @endif

            <rect x="{{ $W - 88 - 214 }}" y="56" width="214" height="46" rx="23" fill="#ffffff" fill-opacity="0.10"/>
            <circle cx="{{ $W - 88 - 190 }}" cy="79" r="6" fill="{{ $expo->isLive() ? '#34d399' : '#94a3b8' }}"/>
            <text x="{{ $W - 88 - 174 }}" y="86" fill="#e2e8f0" font-size="17" font-weight="600">
                @if ($expo->hasEnded())
                    {{ $expo->days }} days · complete
                @elseif ($expo->hasStarted())
                    Day {{ $expo->currentDay() }} of {{ $expo->days }} · live
                @else
                    Starting soon
                @endif
            </text>

            {{-- Hero --}}
            <text x="88" y="232" fill="#ffffff" font-size="132" font-weight="800" letter-spacing="-4">{{ number_format($heroValue) }}</text>
            <text x="88" y="278" fill="#c7d2fe" font-size="27" font-weight="600">{{ $heroLabel }}</text>
            @if ($h['signups'] > 0)
                <text x="88" y="312" fill="#94a3b8" font-size="19">including {{ number_format($h['signups']) }} who joined during the expo</text>
            @endif

            {{-- Stat tiles --}}
            @foreach ($stats as $i => $s)
                @php $sx = 88 + $i * 258; @endphp
                <rect x="{{ $sx }}" y="345" width="234" height="96" rx="18" fill="#ffffff" fill-opacity="0.07"/>
                <rect x="{{ $sx }}" y="345" width="5" height="96" rx="2.5" fill="{{ $s['accent'] }}"/>
                <text x="{{ $sx + 26 }}" y="400" fill="#ffffff" font-size="42" font-weight="700">
                    {{ is_float($s['value']) ? rtrim(rtrim(number_format($s['value'], 1), '0'), '.') : number_format($s['value']) }}{{ $s['suffix'] ?? '' }}
                </text>
                <text x="{{ $sx + 26 }}" y="426" fill="#a5b4fc" font-size="17" font-weight="500">{{ $s['label'] }}</text>
            @endforeach

            {{-- Activity curve --}}
            @if ($hasCurve)
                <path d="{{ $curveArea }}" fill="url(#hcCurve)"/>
                <path d="{{ $curvePath }}" fill="none" stroke="#a5b4fc" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"/>
                <text x="88" y="{{ $cy - 14 }}" fill="#94a3b8" font-size="16" font-weight="600">
                    Activity by hour{{ $h['peakLabel'] ? ' · busiest at '.$h['peakLabel'].' ('.$h['peakCount'].' sign-ins)' : '' }}
                </text>
            @else
                <line x1="{{ $cx }}" y1="{{ $cy + $ch }}" x2="{{ $cx + $cw }}" y2="{{ $cy + $ch }}" stroke="#475569" stroke-width="2"/>
                <text x="88" y="{{ $cy - 14 }}" fill="#94a3b8" font-size="16" font-weight="600">
                    {{ $expo->days }}-{{ \Illuminate\Support\Str::plural('day', $expo->days) }} programme · running round the clock
                </text>
            @endif

            {{-- Footer --}}
            <text x="88" y="{{ $H - 26 }}" fill="#64748b" font-size="15">
                {{ $expo->scheduleLabel() }} · {{ $expo->timezoneLabel() }}
            </text>
            <text x="{{ $W - 88 }}" y="{{ $H - 26 }}" fill="#64748b" font-size="15" text-anchor="end">Connecting teachers with schools</text>
        </svg>
    </div>

    <p class="mt-3 text-xs text-gray-400">
        Exports at {{ $W }}×{{ $H }} — the standard link-preview size for LinkedIn, X and Facebook.
    </p>
</div>

<script>
    (function () {
        const btn = document.getElementById('highlight-download');
        const svg = document.getElementById('highlight-svg');
        if (!btn || !svg) return;

        btn.addEventListener('click', () => {
            const original = btn.innerHTML;
            // The SVG is fully self-contained (logo inlined as a data URI), so
            // it can be rasterised without tainting the canvas.
            const xml = new XMLSerializer().serializeToString(svg);
            const src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(xml);
            const img = new Image();

            img.onload = () => {
                const scale = 2; // retina-quality export
                const canvas = document.createElement('canvas');
                canvas.width = svg.viewBox.baseVal.width * scale;
                canvas.height = svg.viewBox.baseVal.height * scale;
                const ctx = canvas.getContext('2d');
                ctx.scale(scale, scale);
                ctx.drawImage(img, 0, 0);
                canvas.toBlob(blob => {
                    if (!blob) { btn.textContent = 'Export failed'; setTimeout(() => btn.innerHTML = original, 2000); return; }
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'expo-highlights.png';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    setTimeout(() => URL.revokeObjectURL(url), 1000);
                }, 'image/png');
            };

            img.onerror = () => {
                btn.textContent = 'Export failed';
                setTimeout(() => { btn.innerHTML = original; }, 2000);
            };

            img.src = src;
        });
    })();
</script>
