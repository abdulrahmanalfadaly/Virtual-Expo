<x-school-layout title="Dashboard">
    <div class="grid gap-6 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <p class="text-sm text-gray-500">Booth Status</p>
            <p class="mt-2 text-2xl font-semibold {{ $school->is_published ? 'text-green-600' : 'text-amber-600' }}">
                {{ $school->boothStatusLabel() }}
            </p>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <p class="text-sm text-gray-500">Profile Completion</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $school->profileCompletion() }}%</p>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <p class="text-sm text-gray-500">Applications</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $applicationsCount }}</p>
        </div>
    </div>

    @if ($school->status === 'suspended')
        <div class="mt-8 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">
            Your account has been suspended by the administrator. Your booth is hidden from the public site.
        </div>
    @endif

    <div class="mt-12">
        <h2 class="mb-4 text-lg font-semibold text-gray-900">Applications</h2>
        @include('partials.applications-table', [
            'applications' => $applications,
            'cvRouteName' => 'school.applications.cv',
        ])
    </div>

    <div id="booth-editor" class="mt-12">
        <h2 class="mb-4 text-lg font-semibold text-gray-900">Edit your booth</h2>
        @include('partials.booth-editor', [
            'school' => $school,
            'boothSettings' => $boothSettings,
            'updateUrl' => route('school.booth.update'),
            'previewNamePathId' => 'school-preview-name-path',
        ])
    </div>
</x-school-layout>
