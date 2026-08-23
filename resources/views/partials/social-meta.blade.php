@php
    $socialSiteName = \App\Models\SiteSetting::get('site_name', 'Virtual School Expo');
    $socialImagePath = \App\Models\SiteSetting::get('link_preview_image_path') ?: \App\Models\SiteSetting::get('expo_logo_path');
@endphp
<meta property="og:title" content="{{ $socialSiteName }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
@if ($socialImagePath)
    <meta property="og:image" content="{{ url(\Illuminate\Support\Facades\Storage::disk('public')->url($socialImagePath)) }}">
    <meta name="twitter:card" content="summary_large_image">
@endif
