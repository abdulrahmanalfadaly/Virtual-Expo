@props(['class' => ''])

<svg viewBox="0 0 300 400" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet" class="{{ $class }}" {{ $attributes->except('class') }}>
    <rect x="8" y="8" width="284" height="384" rx="18" fill="none" stroke="rgba(255,255,255,0.85)" stroke-width="10" />
    <path d="M8 90 h284" stroke="rgba(255,255,255,0.85)" stroke-width="10" fill="none" />
    <path d="M40 8 L40 60 M110 8 L110 60 M190 8 L190 60 M260 8 L260 60" stroke="rgba(255,255,255,0.55)" stroke-width="6" />
    <rect x="30" y="300" width="240" height="70" rx="10" fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.6)" stroke-width="4" />
</svg>
