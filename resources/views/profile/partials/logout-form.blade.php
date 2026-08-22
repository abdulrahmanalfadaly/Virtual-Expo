<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Log Out') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Sign out of your account on this device.') }}
        </p>
    </header>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <x-secondary-button type="submit">{{ __('Log Out') }}</x-secondary-button>
    </form>
</section>
