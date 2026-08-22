<x-school-layout title="Booth / School Profile">
    @include('partials.booth-editor', [
        'school' => $school,
        'boothSettings' => $boothSettings,
        'updateUrl' => route('school.booth.update'),
        'previewNamePathId' => 'school-preview-name-path',
    ])
</x-school-layout>
