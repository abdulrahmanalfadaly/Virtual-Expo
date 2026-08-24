<x-admin-layout title="Dashboard">
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <p class="text-sm text-gray-500">Total Schools</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $totalSchools }}</p>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <p class="text-sm text-gray-500">Published</p>
            <p class="mt-2 text-2xl font-semibold text-green-600">{{ $publishedSchools }}</p>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <p class="text-sm text-gray-500">Unpublished</p>
            <p class="mt-2 text-2xl font-semibold text-amber-600">{{ $unpublishedSchools }}</p>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <p class="text-sm text-gray-500">Suspended</p>
            <p class="mt-2 text-2xl font-semibold text-red-600">{{ $suspendedSchools }}</p>
        </div>
    </div>

    <div class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <p class="text-sm text-gray-500">Total Applications</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $totalApplications }}</p>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Recent Registrations</h2>
            <div class="mt-4 space-y-3">
                @forelse ($recentSchools as $school)
                    <a href="{{ route('admin.schools.show', $school) }}" class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-gray-50">
                        <span class="text-sm font-medium text-gray-800">{{ $school->name }}</span>
                        <span class="text-xs text-gray-400">{{ $school->created_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">No schools yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Recent Activity</h2>
            <div class="mt-4 space-y-3">
                @forelse ($recentActivity as $log)
                    <div class="rounded-lg px-3 py-2">
                        <p class="text-sm text-gray-800">{{ $log->description }}</p>
                        <p class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No activity yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-base font-semibold text-gray-900">General Settings</h2>
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-4 max-w-xl space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Site / Expo Name</label>
                    <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name']) }}" required class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700">Company / Expo Logo</label>
                    <p class="text-xs text-gray-400">Shown next to the site name in the navigation on every page.</p>
                    @if (! empty($settings['expo_logo_path']))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['expo_logo_path']) }}" class="mt-2 h-12 w-auto max-w-[200px] rounded-lg object-contain ring-1 ring-gray-200">
                    @endif
                    <input type="file" name="expo_logo" accept="image/*" class="mt-2 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700">Link Preview Image</label>
                    <p class="text-xs text-gray-400">Shown when your site's link is shared on WhatsApp, iMessage, social media, etc. Recommended size: 1200×630.</p>
                    @if (! empty($settings['link_preview_image_path']))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['link_preview_image_path']) }}" class="mt-2 h-24 w-full max-w-xs rounded-lg object-cover ring-1 ring-gray-200">
                    @endif
                    <input type="file" name="link_preview_image" accept="image/*" class="mt-2 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Allow New School Registration</p>
                        <p class="text-xs text-gray-500">When disabled, the public registration form is closed.</p>
                    </div>
                    <input type="checkbox" name="allow_registration" value="1" @checked($settings['allow_registration']) class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Allow Applications</p>
                        <p class="text-xs text-gray-500">When disabled, visitors cannot submit new CV applications.</p>
                    </div>
                    <input type="checkbox" name="allow_applications" value="1" @checked($settings['allow_applications']) class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Require Admin Approval</p>
                        <p class="text-xs text-gray-500">When enabled, new schools stay unpublished until an admin manually publishes them. When disabled, new schools publish automatically on signup.</p>
                    </div>
                    <input type="checkbox" name="require_admin_approval" value="1" @checked($settings['require_admin_approval']) class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                </div>
            </div>

            <button class="rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500">Save Settings</button>
        </form>
    </div>
</x-admin-layout>
