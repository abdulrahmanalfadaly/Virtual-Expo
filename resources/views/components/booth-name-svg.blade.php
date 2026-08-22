@props(['name', 'pathId', 'curve' => 120, 'x' => 50, 'y' => 7.3])

@php
    // The arc keeps a fixed width (half-span 404.5, matching the original 140–949 geometry)
    // and is simply recentered at (x%, y%) of the 1089x1444 booth coordinate space, with the
    // curve amount still bending symmetrically around that center — so position and curve are
    // fully independent controls.
    $centerX = ($x / 100) * 1089;
    $centerY = ($y / 100) * 1444;
    $halfWidth = 404.5;
    $startX = $centerX - $halfWidth;
    $endX = $centerX + $halfWidth;
    $half = $curve / 2;
    $endY = $centerY + $half;
    $controlY = $centerY - $half;
    $d = "M {$startX} {$endY} Q {$centerX} {$controlY} {$endX} {$endY}";
@endphp

<svg viewBox="0 0 1089 1444" preserveAspectRatio="xMidYMid meet" class="booth-name-svg" data-booth-name-svg aria-hidden="true">
    <path id="{{ $pathId }}" d="{{ $d }}" fill="none" stroke="none" />
    <text class="booth-name-text" font-size="42" font-weight="600" fill="#1f2937" text-anchor="middle">
        <textPath href="#{{ $pathId }}" startOffset="50%">{{ $name }}</textPath>
    </text>
</svg>
