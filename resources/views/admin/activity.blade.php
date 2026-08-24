<x-admin-layout title="Activity Log">
    <div class="mb-8">
        <h1 class="font-display text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">Activity Log</h1>
        <p class="mt-1 text-sm text-gray-500">Everything happening across schools and admin actions.</p>
    </div>

    <div class="space-y-3">
        @forelse ($logs as $log)
            @if ($log->action === 'school.password_reset_requested' && $resetRequest = $log->passwordResetRequest())
                @include('admin.partials.password-reset-request-card', ['log' => $log, 'resetRequest' => $resetRequest])
            @else
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm text-gray-800">{{ $log->description }}</p>
                        <span class="shrink-0 text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="mt-1 text-xs uppercase tracking-wide text-gray-400">{{ $log->action }}</p>
                </div>
            @endif
        @empty
            <p class="text-sm text-gray-500">No activity recorded yet.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $logs->links() }}</div>
</x-admin-layout>
