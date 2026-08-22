<x-admin-layout title="Schools">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="sr-only">Schools</h2>
        <a href="{{ route('admin.schools.create') }}" class="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500">
            + Add School
        </a>
    </div>

    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name..." class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Statuses</option>
            <option value="published" @selected($status === 'published')>Published</option>
            <option value="unpublished" @selected($status === 'unpublished')>Unpublished</option>
            <option value="suspended" @selected($status === 'suspended')>Suspended</option>
        </select>
        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Registered</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($schools as $school)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">
                            <a href="{{ route('admin.schools.show', $school) }}" class="hover:underline">{{ $school->name }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $school->user->email ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($school->status === 'suspended')
                                <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">Suspended</span>
                            @elseif ($school->is_published)
                                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Published</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Unpublished</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $school->created_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('admin.schools.edit', $school) }}" class="font-medium text-indigo-600 hover:underline">Edit</a>

                                @if ($school->is_published)
                                    <form method="POST" action="{{ route('admin.schools.unpublish', $school) }}">
                                        @csrf
                                        <button class="font-medium text-amber-600 hover:underline">Unpublish</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.schools.publish', $school) }}">
                                        @csrf
                                        <button class="font-medium text-green-600 hover:underline">Publish</button>
                                    </form>
                                @endif

                                @if ($school->status === 'suspended')
                                    <form method="POST" action="{{ route('admin.schools.reactivate', $school) }}">
                                        @csrf
                                        <button class="font-medium text-blue-600 hover:underline">Reactivate</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.schools.suspend', $school) }}">
                                        @csrf
                                        <button class="font-medium text-red-500 hover:underline">Suspend</button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.schools.destroy', $school) }}" onsubmit="return confirm('Permanently delete this school and all its data?')">
                                    @csrf @method('DELETE')
                                    <button class="font-medium text-red-700 hover:underline">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No schools found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $schools->links() }}</div>
</x-admin-layout>
