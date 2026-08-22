<x-admin-layout title="Edit School">
    <div class="mb-6">
        <a href="{{ route('admin.schools.show', $school) }}" class="text-sm font-medium text-indigo-600 hover:underline">&larr; Back to {{ $school->name }}</a>
    </div>

    @include('partials.booth-editor', [
        'school' => $school,
        'boothSettings' => $boothSettings,
        'updateUrl' => route('admin.schools.update', $school),
        'previewNamePathId' => 'admin-edit-preview-name-path',
    ])
</x-admin-layout>
