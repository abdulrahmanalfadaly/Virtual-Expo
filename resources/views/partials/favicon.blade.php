@php
    $faviconPath = \App\Models\SiteSetting::get('expo_logo_path');
@endphp
@if ($faviconPath)
    <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($faviconPath) }}">
@endif
