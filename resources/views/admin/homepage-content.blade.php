<x-admin-layout title="Homepage Content">
    <form method="POST" action="{{ route('admin.homepage.update') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Branding &amp; Hero</h2>
            <div class="mt-4 grid gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hero Headline</label>
                    <input type="text" name="hero_headline" value="{{ old('hero_headline', $settings['hero_headline']) }}" required class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hero Description</label>
                    <textarea name="hero_description" rows="3" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('hero_description', $settings['hero_description']) }}</textarea>
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700">Hero Overlay Opacity</label>
                        <span class="text-sm font-semibold text-indigo-600"><span id="hero-opacity-value">{{ $settings['hero_overlay_opacity'] ?? 70 }}</span>%</span>
                    </div>
                    <p class="text-xs text-gray-400">How opaque the hero gradient is over the website background.</p>
                    <input type="range" name="hero_overlay_opacity" id="hero-opacity" min="0" max="100" step="5" value="{{ old('hero_overlay_opacity', $settings['hero_overlay_opacity'] ?? 70) }}" class="mt-1 w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Company / Expo Logo</label>
                    @if (! empty($settings['expo_logo_path']))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['expo_logo_path']) }}" class="mt-2 h-12 w-12 rounded-lg object-contain ring-1 ring-gray-200">
                    @endif
                    <input type="file" name="expo_logo" accept="image/*" class="mt-2 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Website Background Image</label>
                    <p class="text-xs text-gray-400">Fixed background shown behind the homepage as visitors scroll.</p>
                    @if (! empty($settings['site_background_path']))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['site_background_path']) }}" class="mt-2 h-16 w-full max-w-xs rounded-lg object-cover ring-1 ring-gray-200">
                    @endif
                    <input type="file" name="site_background" accept="image/*" class="mt-2 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-base font-semibold text-gray-900">About Section</h2>
            <textarea name="about_content" rows="5" class="mt-4 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('about_content', $settings['about_content']) }}</textarea>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Contact &amp; Support</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone / WhatsApp</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <input type="text" name="contact_address" value="{{ old('contact_address', $settings['contact_address']) }}" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Support Info</label>
                    <textarea name="support_info" rows="2" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('support_info', $settings['support_info']) }}</textarea>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Footer</h2>
            <input type="text" name="footer_text" value="{{ old('footer_text', $settings['footer_text']) }}" class="mt-4 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <button class="rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500">Save Homepage Content</button>
    </form>

    <script>
        document.getElementById('hero-opacity')?.addEventListener('input', (e) => {
            document.getElementById('hero-opacity-value').textContent = e.target.value;
        });
    </script>
</x-admin-layout>
