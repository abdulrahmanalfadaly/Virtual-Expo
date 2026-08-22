<x-admin-layout title="Notifications">
    <div class="space-y-3">
        @forelse ($notifications as $notification)
            <a href="{{ route('admin.notifications.read', $notification->id) }}" class="block rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100 hover:bg-gray-50 {{ $notification->read_at ? 'opacity-60' : '' }}">
                <p class="text-sm text-gray-800">{{ $notification->data['description'] ?? '' }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
            </a>
        @empty
            <p class="text-sm text-gray-500">No notifications yet.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $notifications->links() }}</div>
</x-admin-layout>
