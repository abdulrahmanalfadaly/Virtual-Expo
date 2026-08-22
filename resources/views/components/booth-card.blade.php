@props(['school', 'boothSettings'])

@php
    $logoX = $boothSettings['booth_logo_x'] ?? 50;
    $logoY = $boothSettings['booth_logo_y'] ?? 71.5;
    $logoW = $boothSettings['booth_logo_width'] ?? 48;
    $logoH = $boothSettings['booth_logo_max_height'] ?? 13;
    $initials = \Illuminate\Support\Str::of($school->name)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp

<button type="button"
    class="group text-left w-full booth-card-trigger focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 rounded-2xl"
    data-modal-target="school-modal-{{ $school->id }}"
    aria-haspopup="dialog">
    <div class="booth-frame">
        @if (! empty($boothSettings['booth_template_path']))
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($boothSettings['booth_template_path']) }}" class="booth-template-img" alt="" loading="lazy">
        @else
            <x-booth-placeholder-svg class="booth-template-svg" />
        @endif

        @if ($school->logo_path)
            <img src="{{ $school->logoUrl() }}" alt="{{ $school->name }} logo" loading="lazy" class="booth-logo"
                style="top:{{ $logoY }}%; left:{{ $logoX }}%; width:{{ $logoW }}%; max-height:{{ $logoH }}%;">
        @else
            <div class="booth-logo-placeholder" style="top:{{ $logoY }}%; left:{{ $logoX }}%; width:{{ $logoW }}%; height:{{ $logoH }}%; font-size: clamp(0.75rem, 4vw, 1.5rem);">
                {{ $initials }}
            </div>
        @endif

        <x-booth-name-svg :name="$school->name" :path-id="'booth-name-path-'.$school->id" :curve="$boothSettings['booth_name_curve'] ?? 120"
            :x="$boothSettings['booth_name_x'] ?? 50" :y="$boothSettings['booth_name_y'] ?? 7.3" />
    </div>
</button>
