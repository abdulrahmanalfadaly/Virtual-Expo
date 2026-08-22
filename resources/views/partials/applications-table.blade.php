@php
    $cvRouteName = $cvRouteName ?? 'school.applications.cv';
@endphp

<div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3">Applicant</th>
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
                        <a href="{{ route($cvRouteName, $application) }}" class="font-medium text-indigo-600 hover:underline">Download CV</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No applications received yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $applications->links() }}</div>
