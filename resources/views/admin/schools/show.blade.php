<x-admin-layout title="School Details">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="font-display text-2xl font-semibold text-gray-900">{{ $school->name }}</h2>
            <p class="text-sm text-gray-500">{{ $school->user->email ?? '' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.schools.edit', $school) }}" class="rounded-full border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Edit</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Profile</h3>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">School Type</dt><dd class="text-gray-900">{{ $school->schoolTypeLabel() }}</dd></div>
                    <div><dt class="text-gray-500">Contact Person</dt><dd class="text-gray-900">{{ $school->contact_person ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Contact Email</dt><dd class="text-gray-900">{{ $school->contact_email ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Phone</dt><dd class="text-gray-900">{{ $school->contact_phone ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Website</dt><dd class="text-gray-900">{{ $school->website ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Address</dt><dd class="text-gray-900">{{ $school->address ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Short Description</dt><dd class="text-gray-900">{{ $school->short_description ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Programs ({{ $school->programs->count() }})</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($school->programs as $program)
                        <li class="text-gray-800">• {{ $program->title }}</li>
                    @empty
                        <li class="text-gray-500">No programs added.</li>
                    @endforelse
                </ul>
                <a href="{{ route('admin.schools.programs.index', $school) }}" class="mt-3 inline-block text-sm font-medium text-indigo-600 hover:underline">Manage programs &rarr;</a>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Gallery ({{ $school->galleryImages->count() }})</h3>
                <a href="{{ route('admin.schools.gallery.index', $school) }}" class="mt-2 inline-block text-sm font-medium text-indigo-600 hover:underline">Manage gallery &rarr;</a>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Applications ({{ $school->applications->count() }})</h3>
                <a href="{{ route('admin.applications.index', ['school' => $school->id]) }}" class="mt-2 inline-block text-sm font-medium text-indigo-600 hover:underline">View all applications &rarr;</a>
            </div>
        </div>

        <div>
            <div class="booth-frame mx-auto max-w-xs">
                @if (! empty($boothSettings['booth_template_path']))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($boothSettings['booth_template_path']) }}" class="booth-template-img" alt="">
                @else
                    <x-booth-placeholder-svg class="booth-template-svg" />
                @endif
                @if ($school->logo_path)
                    <img src="{{ $school->logoUrl() }}" alt="" class="booth-logo"
                        style="top:{{ $boothSettings['booth_logo_y'] ?? 71.5 }}%; left:{{ $boothSettings['booth_logo_x'] ?? 50 }}%; width:{{ $boothSettings['booth_logo_width'] ?? 48 }}%; max-height:{{ $boothSettings['booth_logo_max_height'] ?? 13 }}%;">
                @endif
                <x-booth-name-svg :name="$school->name" path-id="admin-show-name-path" :curve="$boothSettings['booth_name_curve'] ?? 120"
                    :x="$boothSettings['booth_name_x'] ?? 50" :y="$boothSettings['booth_name_y'] ?? 7.3" />
            </div>
        </div>
    </div>
</x-admin-layout>
