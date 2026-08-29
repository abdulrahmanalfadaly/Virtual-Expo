@php
    $previewNamePathId = $previewNamePathId ?? 'booth-editor-preview-name-path';
@endphp

<div class="grid gap-8 lg:grid-cols-3">
    <form method="POST" action="{{ $updateUrl }}" enctype="multipart/form-data" class="space-y-6 lg:col-span-2">
        @csrf
        @method('PUT')

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Basic Information
            </h2>

            <div class="mt-5">
                <label class="block text-sm font-medium text-gray-700">School Name</label>
                <input type="text" name="name" id="preview-name" value="{{ old('name', $school->name) }}" required class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">School Type</label>
                <select name="school_type" required class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="" disabled @selected(! old('school_type', $school->school_type))>Select a type</option>
                    <option value="national" @selected(old('school_type', $school->school_type) === 'national')>National</option>
                    <option value="international" @selected(old('school_type', $school->school_type) === 'international')>International</option>
                    <option value="online" @selected(old('school_type', $school->school_type) === 'online')>Online</option>
                </select>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Logo</label>
                @if ($school->logo_path)
                    <div class="mt-1 mb-3 flex items-center gap-3">
                        <img src="{{ $school->logoUrl() }}" alt="" class="h-14 w-14 shrink-0 rounded-lg object-contain ring-1 ring-gray-200">
                        <a href="{{ route('schools.logo.download', $school) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                           title="Download the current logo">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Download current logo
                        </a>
                    </div>
                @endif
                <input type="file" name="logo" id="logo-input" accept="image/*" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-gray-400">PNG/JPG recommended, max 2MB.</p>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                About
            </h2>
            <textarea name="full_description" rows="6" class="mt-5 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('full_description', $school->full_description) }}</textarea>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                </svg>
                Media &amp; Links
            </h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
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

        <button type="submit" class="rounded-full bg-indigo-600 px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">
            Save Changes
        </button>
    </form>

    <div>
        <div class="sticky top-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Live Preview
            </h2>
            <div class="mx-auto mt-5 max-w-xs">
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
