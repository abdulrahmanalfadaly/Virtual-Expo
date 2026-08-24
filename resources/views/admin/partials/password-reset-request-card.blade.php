@php
    $decryptedPassword = $resetRequest->decryptedPassword();
@endphp

<div class="rounded-xl border-2 border-violet-200 bg-violet-50 p-4">
    <div class="flex items-center justify-between gap-3">
        <p class="flex items-center gap-2 text-sm font-semibold text-violet-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            Password Reset Request
        </p>
        <span class="shrink-0 text-xs text-violet-400">{{ $log->created_at->diffForHumans() }}</span>
    </div>

    <dl class="mt-3 grid gap-2 text-sm text-violet-900 sm:grid-cols-3">
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-violet-400">School</dt>
            <dd class="font-medium">{{ $resetRequest->school->name ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-violet-400">Email</dt>
            <dd class="font-medium">{{ $resetRequest->email }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-violet-400">Requested Password</dt>
            <dd class="font-mono font-medium">{{ $decryptedPassword ?? 'already handled' }}</dd>
        </div>
    </dl>

    @if ($resetRequest->isPending())
        <form method="POST" action="{{ route('admin.password-reset-requests.approve', $resetRequest) }}" class="mt-4">
            @csrf
            <button class="rounded-full bg-violet-600 px-4 py-1.5 text-xs font-semibold text-white shadow transition hover:bg-violet-500">
                Approve &amp; Change Password
            </button>
        </form>
    @else
        <p class="mt-4 text-xs font-medium text-violet-500">
            Approved {{ $resetRequest->resolved_at?->diffForHumans() }}{{ $resetRequest->resolvedBy ? ' by '.$resetRequest->resolvedBy->name : '' }}.
        </p>
    @endif
</div>
