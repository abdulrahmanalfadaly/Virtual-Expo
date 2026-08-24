<x-admin-layout title="Edit Teacher">
    <div class="mb-6">
        <a href="{{ route('admin.teachers.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">&larr; Back to Teachers</a>
    </div>

    <div class="max-w-2xl space-y-6">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Teacher Info</h2>
            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-gray-500">Name</dt><dd class="text-gray-900">{{ $teacher->user->name }}</dd></div>
                <div><dt class="text-gray-500">Email</dt><dd class="text-gray-900">{{ $teacher->user->email }}</dd></div>
                <div><dt class="text-gray-500">Phone</dt><dd class="text-gray-900">{{ $teacher->phone }}</dd></div>
                <div><dt class="text-gray-500">Status</dt><dd class="text-gray-900">{{ ucfirst($teacher->status) }}</dd></div>
            </dl>
        </div>

        <div>
            <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                </svg>
                Reset Password
            </h2>
            <form method="POST" action="{{ route('admin.teachers.update-password', $teacher) }}" class="mt-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">New Password</label>
                        <input type="password" name="password" required autocomplete="new-password" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <button class="mt-5 rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500">Update Password</button>
            </form>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Account</h2>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                @if ($teacher->status === 'suspended')
                    <form method="POST" action="{{ route('admin.teachers.reactivate', $teacher) }}">
                        @csrf
                        <button class="rounded-full border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-600 hover:bg-blue-50">Reactivate</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.teachers.suspend', $teacher) }}">
                        @csrf
                        <button class="rounded-full border border-amber-200 px-4 py-2 text-sm font-semibold text-amber-600 hover:bg-amber-50">Suspend</button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" onsubmit="return confirm('Permanently delete this teacher account?')">
                    @csrf @method('DELETE')
                    <button class="rounded-full border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Delete Account</button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
