@php
    /**
     * A short, share-ready card: the platform's name, the headline sign-in
     * figure, and the shape of the activity behind it. Every number is a real
     * count. Fixed canvas so it exports cleanly to a social-friendly PNG.
     */
    $h = $highlights;

    // Always the live site name, so rebranding flows through automatically.
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

    $W = 1200; $H = 480;

    $series = array_values($h['series'] ?? []);
    $seriesMax = max(1, (int) (count($series) ? max($series) : 0));
    $hasCurve = count($series) > 1 && array_sum($series) > 0;

    // Chart band across the lower half.
    $cx = 80; $cy = 250; $cw = $W - 160; $ch = 150;
    $baseline = $cy + $ch;

    $curvePath = '';
    $curveArea = '';
    $peakPoint = null;

    if ($hasCurve) {
        $count = count($series);
        $peakIndex = array_search($seriesMax, $series, true);

        foreach ($series as $i => $v) {
            $x = $cx + ($count === 1 ? $cw / 2 : ($i / ($count - 1)) * $cw);
            $y = $baseline - ($v / $seriesMax) * $ch;
            $curvePath .= ($i === 0 ? 'M ' : ' L ').sprintf('%.1f %.1f', $x, $y);

            if ($i === $peakIndex) {
                $peakPoint = ['x' => $x, 'y' => $y];
            }
        }

        $curveArea = $curvePath.sprintf(' L %.1f %.1f L %.1f %.1f Z', $cx + $cw, $baseline, $cx, $baseline);
    }
@endphp

<div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold" style="color: var(--ink);">Highlights</h2>
            <p class="text-sm text-gray-500">A share-ready card for the selected range — every number is a real count.</p>
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
             role="img" aria-label="{{ $siteName }} — {{ number_format($h['logins']) }} total sign-ins">

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
                    <stop offset="0%" stop-color="#6366f1" stop-opacity="0.45"/>
                    <stop offset="100%" stop-color="#6366f1" stop-opacity="0"/>
                </radialGradient>
            </defs>

            <rect width="{{ $W }}" height="{{ $H }}" fill="url(#hcBg)"/>
            <ellipse cx="1010" cy="70" rx="360" ry="230" fill="url(#hcGlow)"/>
            <ellipse cx="140" cy="440" rx="300" ry="180" fill="url(#hcGlow)" opacity="0.55"/>

            {{-- Brand --}}
            @if ($logoData)
                <image href="{{ $logoData }}" x="80" y="46" height="52" width="52" preserveAspectRatio="xMidYMid meet"/>
                <text x="146" y="82" fill="#ffffff" font-size="26" font-weight="700">{{ $siteName }}</text>
            @else
                <text x="80" y="82" fill="#ffffff" font-size="28" font-weight="700">{{ $siteName }}</text>
            @endif

            {{-- Hero: total sign-ins --}}
            <text x="80" y="196" fill="#ffffff" font-size="118" font-weight="800" letter-spacing="-4">{{ number_format($h['logins']) }}</text>
            <text x="80" y="232" fill="#c7d2fe" font-size="26" font-weight="600">total sign-ins</text>

            {{-- Activity curve --}}
            @if ($hasCurve)
                <line x1="{{ $cx }}" y1="{{ $baseline }}" x2="{{ $cx + $cw }}" y2="{{ $baseline }}" stroke="#ffffff" stroke-opacity="0.12" stroke-width="2"/>
                <path d="{{ $curveArea }}" fill="url(#hcCurve)"/>
                <path d="{{ $curvePath }}" fill="none" stroke="#a5b4fc" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"/>
                @if ($peakPoint)
                    <circle cx="{{ $peakPoint['x'] }}" cy="{{ $peakPoint['y'] }}" r="7" fill="#a5b4fc" stroke="#1e1b4b" stroke-width="3"/>
                @endif
            @else
                <line x1="{{ $cx }}" y1="{{ $baseline }}" x2="{{ $cx + $cw }}" y2="{{ $baseline }}" stroke="#ffffff" stroke-opacity="0.12" stroke-width="2"/>
            @endif
        </svg>
    </div>

    <p class="mt-3 text-xs text-gray-400">
        Exports at {{ $W }}×{{ $H }}, sized for social posts.
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
            // it rasterises without tainting the canvas.
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
                    if (!blob) { btn.textContent = 'Export failed'; setTimeout(() => { btn.innerHTML = original; }, 2000); return; }
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'highlights.png';
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
