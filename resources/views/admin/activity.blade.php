@php
    $maxAction = max(1, (int) (collect($breakdown)->max('total') ?? 1));
@endphp

<x-admin-layout title="Activity Log">
    <div class="mb-8">
        <h1 class="font-display text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">Activity Log</h1>
        <p class="mt-1 text-sm text-gray-500">
            Everything happening across schools and admin actions.
            Times shown in {{ $clock->timezone }} ({{ $clock->label() }}).
        </p>
    </div>

    {{-- All recorded activity, first: what the log is made of --}}
    <div class="mb-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <h2 class="text-base font-semibold text-gray-900">All recorded activity</h2>
        <p class="text-sm text-gray-500">Every action type ever recorded, most frequent first.</p>

        @if (empty($breakdown))
            <p class="mt-8 mb-4 text-center text-sm text-gray-400">Nothing recorded yet.</p>
        @else
            <ul class="mt-5 space-y-3">
                @foreach ($breakdown as $item)
                    <li>
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <span class="truncate text-gray-700">{{ $item['label'] }}</span>
                            <span class="flex shrink-0 items-baseline gap-3">
                                <span class="text-xs text-gray-400">{{ $item['lastAt']->diffForHumans() }}</span>
                                <span class="text-xs text-gray-400" style="font-variant-numeric: tabular-nums;">{{ $item['share'] }}%</span>
                                <span class="w-10 text-right font-semibold text-gray-900" style="font-variant-numeric: tabular-nums;">{{ number_format($item['total']) }}</span>
                            </span>
                        </div>
                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-indigo-500" style="width: {{ max(1.5, $item['total'] / $maxAction * 100) }}%;"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <h2 class="mb-3 text-base font-semibold text-gray-900">Full log</h2>

    <div class="space-y-3">
        @forelse ($logs as $log)
            @if ($log->action === 'school.password_reset_requested' && $resetRequest = $log->passwordResetRequest())
                @include('admin.partials.password-reset-request-card', ['log' => $log, 'resetRequest' => $resetRequest])
            @else
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm text-gray-800">{{ $log->description }}</p>
                        <span class="shrink-0 text-xs text-gray-400" title="{{ $clock->local($log->created_at)?->format('D j M Y, g:i A') }}">
                            {{ $log->created_at->diffForHumans() }}
                        </span>
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
