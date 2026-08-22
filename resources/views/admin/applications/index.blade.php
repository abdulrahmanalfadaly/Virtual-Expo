<x-admin-layout title="Applications">
    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <select name="school" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Schools</option>
            @foreach ($schools as $school)
                <option value="{{ $school->id }}" @selected($selectedSchool == $school->id)>{{ $school->name }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Applicant</th>
                    <th class="px-4 py-3">School</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Message</th>
                    <th class="px-4 py-3">Submitted</th>
                    <th class="px-4 py-3">CV</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($applications as $application)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $application->applicant_name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $application->school->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $application->applicant_email }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $application->applicant_phone ?: '—' }}</td>
                        <td class="max-w-xs px-4 py-3 text-gray-600">
                            @if ($application->message)
                                <span class="line-clamp-2" title="{{ $application->message }}">{{ $application->message }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $application->created_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.applications.cv', $application) }}" class="font-medium text-indigo-600 hover:underline">Download CV</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $applications->links() }}</div>
</x-admin-layout>
