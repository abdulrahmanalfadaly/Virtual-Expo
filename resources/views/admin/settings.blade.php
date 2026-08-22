<x-admin-layout title="General Settings">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-xl space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <div>
                <label class="block text-sm font-medium text-gray-700">Site / Expo Name</label>
                <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name']) }}" required class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
        </div>

        <button class="rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500">Save Settings</button>
    </form>
</x-admin-layout>
