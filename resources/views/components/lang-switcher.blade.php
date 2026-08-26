@php
    $currentLocale = app()->getLocale();
@endphp

<div class="inline-flex items-center gap-1 rounded-full bg-white/10 p-1 text-xs font-semibold">
    <a href="{{ route('lang.switch', 'en') }}"
       class="rounded-full px-2.5 py-1 transition {{ $currentLocale === 'en' ? 'bg-white text-gray-900' : 'text-gray-300 hover:text-white' }}">
        EN
    </a>
    <a href="{{ route('lang.switch', 'ar') }}"
       class="rounded-full px-2.5 py-1 transition {{ $currentLocale === 'ar' ? 'bg-white text-gray-900' : 'text-gray-300 hover:text-white' }}">
        AR
    </a>
</div>
