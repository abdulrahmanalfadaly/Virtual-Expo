<x-school-layout title="Dashboard">
    <div class="grid gap-6 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <p class="text-sm text-gray-500">Booth Status</p>
            <p class="mt-2 text-2xl font-semibold {{ $school->is_published ? 'text-green-600' : 'text-amber-600' }}">
                {{ $school->is_published ? 'Published' : 'Draft' }}
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

    <div class="mt-8 flex flex-wrap gap-3">
        <a href="#booth-editor" class="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500">
            Edit Booth
        </a>
        <a href="{{ route('home') }}#schools" target="_blank" class="rounded-full border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Preview Public Homepage
        </a>

        @if ($school->is_published)
            <form method="POST" action="{{ route('school.booth.unpublish') }}">
                @csrf
                <button class="rounded-full border border-amber-300 px-5 py-2.5 text-sm font-semibold text-amber-700 hover:bg-amber-50">Unpublish Booth</button>
            </form>
        @else
            <form method="POST" action="{{ route('school.booth.publish') }}">
                @csrf
                <button class="rounded-full bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-green-500">Publish Booth</button>
            </form>
        @endif
    </div>

    @if ($school->status === 'suspended')
        <div class="mt-8 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">
            Your account has been suspended by the administrator. Your booth is hidden from the public site.
        </div>
    @endif

    <div id="booth-editor" class="mt-12">
        <h2 class="mb-4 text-lg font-semibold text-gray-900">Booth / Profile</h2>
        @include('partials.booth-editor', [
            'school' => $school,
            'boothSettings' => $boothSettings,
            'updateUrl' => route('school.booth.update'),
            'previewNamePathId' => 'school-preview-name-path',
        ])
    </div>

    <div class="mt-12">
        <h2 class="mb-4 text-lg font-semibold text-gray-900">Applications</h2>
        @include('partials.applications-table', [
            'applications' => $applications,
            'cvRouteName' => 'school.applications.cv',
        ])
    </div>
</x-school-layout>
