@php
    $statusColors = match (true) {
        $school->is_published => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'dot' => 'bg-emerald-500'],
        (bool) $school->approved_at => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'dot' => 'bg-amber-500'],
        default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'dot' => 'bg-gray-400'],
    };
    $completion = $school->profileCompletion();
@endphp

<x-school-layout title="Dashboard">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">Welcome back, {{ $school->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">Manage your booth and track applications from one place.</p>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full {{ $statusColors['bg'] }} px-4 py-2 text-sm font-semibold {{ $statusColors['text'] }}">
            <span class="h-2 w-2 rounded-full {{ $statusColors['dot'] }}"></span>
            {{ $school->boothStatusLabel() }}
        </span>
    </div>

    <div class="grid gap-5 sm:grid-cols-3">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $statusColors['bg'] }} {{ $statusColors['text'] }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1m-6 4h1m4 0h1m-6 4h1m4 0h1" />
                </svg>
            </span>
            <p class="mt-4 text-sm text-gray-500">Booth Status</p>
            <p class="mt-1 text-2xl font-semibold {{ $statusColors['text'] }}">{{ $school->boothStatusLabel() }}</p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <div class="flex items-center gap-4">
                <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-full" style="background: conic-gradient(#4f46e5 {{ $completion * 3.6 }}deg, #e5e7eb 0deg);">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-xs font-bold text-gray-900">{{ $completion }}%</div>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Profile Completion</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $completion }}%</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </span>
            <p class="mt-4 text-sm text-gray-500">Applications</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $applicationsCount }}</p>
        </div>
    </div>

    @if ($school->status === 'suspended')
        <div class="mt-8 flex items-start gap-3 rounded-2xl bg-red-50 p-5 text-sm text-red-800 ring-1 ring-red-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <p>Your account has been suspended by the administrator. Your booth is hidden from the public site.</p>
        </div>
    @endif

    <div class="mt-12">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="flex items-center gap-2 font-display text-xl font-semibold text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                Applications
            </h2>
            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">{{ $applicationsCount }} total</span>
        </div>
        @include('partials.applications-table', [
            'applications' => $applications,
            'cvRouteName' => 'school.applications.cv',
        ])
    </div>

    <div id="booth-editor" class="mt-12">
        <h2 class="mb-5 flex items-center gap-2 font-display text-xl font-semibold text-gray-900">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
            </svg>
            Edit your booth
        </h2>
        @include('partials.booth-editor', [
            'school' => $school,
            'boothSettings' => $boothSettings,
            'updateUrl' => route('school.booth.update'),
            'previewNamePathId' => 'school-preview-name-path',
        ])
    </div>
</x-school-layout>
