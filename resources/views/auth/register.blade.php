<x-guest-layout>
    <h2 class="mb-6 text-center text-xl font-semibold text-gray-900 dark:text-white">Register Your School</h2>

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
        @csrf

        <div>
            <x-input-label for="school_name" :value="__('School Name')" />
            <x-text-input id="school_name" class="block mt-1 w-full" type="text" name="school_name" :value="old('school_name')" required autofocus />
            <x-input-error :messages="$errors->get('school_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="contact_person" :value="__('Contact Person Name')" />
            <x-text-input id="contact_person" class="block mt-1 w-full" type="text" name="contact_person" :value="old('contact_person')" required />
            <x-input-error :messages="$errors->get('contact_person')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="logo" :value="__('School Logo')" />
            <input id="logo" type="file" name="logo" accept="image/*" class="mt-1 block w-full text-sm text-gray-300 file:mr-3 file:rounded-md file:border-0 file:bg-gray-700 file:px-3 file:py-1.5 file:text-sm file:text-gray-100">
            <x-input-error :messages="$errors->get('logo')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="full_description" :value="__('About')" />
            <textarea id="full_description" name="full_description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">{{ old('full_description') }}</textarea>
            <x-input-error :messages="$errors->get('full_description')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="video_url" :value="__('YouTube Video URL')" />
            <x-text-input id="video_url" class="block mt-1 w-full" type="url" name="video_url" :value="old('video_url')" placeholder="https://youtube.com/..." />
            <x-input-error :messages="$errors->get('video_url')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="zoom_url" :value="__('Zoom / Meeting URL')" />
            <x-text-input id="zoom_url" class="block mt-1 w-full" type="url" name="zoom_url" :value="old('zoom_url')" />
            <x-input-error :messages="$errors->get('zoom_url')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register School') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
