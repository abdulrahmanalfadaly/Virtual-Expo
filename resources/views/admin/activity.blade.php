<x-admin-layout title="Activity Log">
    <div class="space-y-3">
        @forelse ($logs as $log)
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-800">{{ $log->description }}</p>
                    <span class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                </div>
                <p class="mt-1 text-xs uppercase tracking-wide text-gray-400">{{ $log->action }}</p>
            </div>
        @empty
            <p class="text-sm text-gray-500">No activity recorded yet.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $logs->links() }}</div>
</x-admin-layout>
