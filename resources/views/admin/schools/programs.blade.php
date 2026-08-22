<x-admin-layout title="Programs — {{ $school->name }}">
    <div class="mb-6">
        <a href="{{ route('admin.schools.show', $school) }}" class="text-sm font-medium text-indigo-600 hover:underline">&larr; Back to {{ $school->name }}</a>
    </div>

    <div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <h2 class="text-base font-semibold text-gray-900">Add Program</h2>
        <form method="POST" action="{{ route('admin.schools.programs.store', $school) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" required class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Short Description</label>
                <input type="text" name="description" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="sm:col-span-2">
                <button class="rounded-full bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">Add Program</button>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse ($programs as $program)
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100" x-data="{ editing: false }">
                <div x-show="!editing" class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $program->title }}</p>
                        @if ($program->description)
                            <p class="mt-1 text-sm text-gray-600">{{ $program->description }}</p>
                        @endif
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <button @click="editing = true" class="text-sm font-medium text-indigo-600 hover:underline">Edit</button>
                        <form method="POST" action="{{ route('admin.schools.programs.destroy', [$school, $program]) }}" onsubmit="return confirm('Delete this program?')">
                            @csrf @method('DELETE')
                            <button class="text-sm font-medium text-red-600 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>

                <form x-show="editing" method="POST" action="{{ route('admin.schools.programs.update', [$school, $program]) }}" class="grid gap-3 sm:grid-cols-2">
                    @csrf @method('PUT')
                    <input type="text" name="title" value="{{ $program->title }}" required class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <input type="text" name="description" value="{{ $program->description }}" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <div class="sm:col-span-2 flex gap-2">
                        <button class="rounded-full bg-indigo-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
                        <button type="button" @click="editing = false" class="rounded-full border border-gray-300 px-4 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-500">No programs added yet.</p>
        @endforelse
    </div>
</x-admin-layout>
