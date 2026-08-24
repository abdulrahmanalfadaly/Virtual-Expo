<x-admin-layout title="Teachers">
    <div class="mb-6">
        <h1 class="font-display text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">Teachers</h1>
        <p class="mt-1 text-sm text-gray-500">Registered teachers who can browse and apply to schools.</p>
    </div>

    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email..." class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Statuses</option>
            <option value="active" @selected($status === 'active')>Active</option>
            <option value="suspended" @selected($status === 'suspended')>Suspended</option>
        </select>
        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50/80 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-5 py-3.5">Name</th>
                    <th class="px-5 py-3.5">Email</th>
                    <th class="px-5 py-3.5">Phone</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5">Registered</th>
                    <th class="px-5 py-3.5">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($teachers as $teacher)
                    <tr class="transition hover:bg-gray-50/60">
                        <td class="px-5 py-3.5 font-medium text-gray-900">{{ $teacher->user->name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $teacher->user->email ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $teacher->phone }}</td>
                        <td class="px-5 py-3.5">
                            @if ($teacher->status === 'suspended')
                                <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">Suspended</span>
                            @else
                                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Active</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $teacher->created_at->format('M j, Y') }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('admin.teachers.edit', $teacher) }}" class="font-medium text-indigo-600 hover:underline">Reset Password</a>

                                @if ($teacher->status === 'suspended')
                                    <form method="POST" action="{{ route('admin.teachers.reactivate', $teacher) }}">
                                        @csrf
                                        <button class="font-medium text-blue-600 hover:underline">Reactivate</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.teachers.suspend', $teacher) }}">
                                        @csrf
                                        <button class="font-medium text-red-500 hover:underline">Suspend</button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" onsubmit="return confirm('Permanently delete this teacher account?')">
                                    @csrf @method('DELETE')
                                    <button class="font-medium text-red-700 hover:underline">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">No teachers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $teachers->links() }}</div>
</x-admin-layout>
