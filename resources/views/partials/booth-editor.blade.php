@php
    $previewNamePathId = $previewNamePathId ?? 'booth-editor-preview-name-path';
@endphp

<div class="grid gap-8 lg:grid-cols-3">
    <form method="POST" action="{{ $updateUrl }}" enctype="multipart/form-data" class="space-y-6 lg:col-span-2">
        @csrf
        @method('PUT')

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Basic Information</h2>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">School Name</label>
                <input type="text" name="name" id="preview-name" value="{{ old('name', $school->name) }}" required class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Logo</label>
                @if ($school->logo_path)
                    <div class="mt-1 mb-2">
                        <img src="{{ $school->logoUrl() }}" alt="" class="h-14 w-14 rounded-lg object-contain ring-1 ring-gray-200">
                    </div>
                @endif
                <input type="file" name="logo" id="logo-input" accept="image/*" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-gray-400">PNG/JPG recommended, max 2MB.</p>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-base font-semibold text-gray-900">About</h2>
            <textarea name="full_description" rows="6" class="mt-4 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('full_description', $school->full_description) }}</textarea>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Media &amp; Links</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">YouTube Video URL</label>
                    <input type="url" name="video_url" value="{{ old('video_url', $school->video_url) }}" placeholder="https://youtube.com/..." class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Zoom / Meeting URL</label>
                    <input type="url" name="zoom_url" value="{{ old('zoom_url', $school->zoom_url) }}" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500">
            Save Changes
        </button>
    </form>

    <div>
        <div class="sticky top-6">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Live Preview</h2>
            <div class="mx-auto max-w-xs">
                <div class="booth-frame" id="preview-frame">
                    @if (! empty($boothSettings['booth_template_path']))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($boothSettings['booth_template_path']) }}" class="booth-template-img" alt="">
                    @else
                        <x-booth-placeholder-svg class="booth-template-svg" />
                    @endif
                    <img id="preview-logo" @if($school->logo_path) src="{{ $school->logoUrl() }}" @endif alt="" class="booth-logo {{ $school->logo_path ? '' : 'hidden' }}"
                        style="top:{{ $boothSettings['booth_logo_y'] ?? 71.5 }}%; left:{{ $boothSettings['booth_logo_x'] ?? 50 }}%; width:{{ $boothSettings['booth_logo_width'] ?? 48 }}%; max-height:{{ $boothSettings['booth_logo_max_height'] ?? 13 }}%;">
                    <x-booth-name-svg :name="$school->name" :path-id="$previewNamePathId" :curve="$boothSettings['booth_name_curve'] ?? 120"
                        :x="$boothSettings['booth_name_x'] ?? 50" :y="$boothSettings['booth_name_y'] ?? 7.3" />
                </div>
            </div>
            <p class="mt-3 text-center text-xs text-gray-400">This is how the booth appears on the public homepage.</p>
        </div>
    </div>
</div>

<script>
    document.getElementById('preview-name')?.addEventListener('input', (e) => {
        const svg = document.getElementById('preview-frame').querySelector('[data-booth-name-svg]');
        const textPath = svg.querySelector('textPath');
        textPath.textContent = e.target.value;
        window.fitBoothCurvedText(svg);
    });
    document.getElementById('logo-input')?.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (! file) return;
        const reader = new FileReader();
        reader.onload = (ev) => {
            const img = document.getElementById('preview-logo');
            img.src = ev.target.result;
            img.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    });
</script>
