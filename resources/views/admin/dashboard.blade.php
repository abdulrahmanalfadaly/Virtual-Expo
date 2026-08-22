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
</x-admin-layout>
