@php
    $initialTab = old('tab', request('as') === 'school' ? 'school' : 'teacher');
@endphp

<x-guest-layout>
    <div x-data="{
            tab: '{{ $initialTab }}',
            setHeight() {
                const panel = this.tab === 'teacher' ? this.$refs.teacherPanel : this.$refs.schoolPanel;
                this.$refs.stage.style.height = panel.offsetHeight + 'px';
            }
         }"
         x-init="$nextTick(() => setHeight()); $watch('tab', () => $nextTick(() => setHeight()))"
         x-cloak>
        <h2 class="mb-6 text-center text-xl font-semibold text-gray-900 dark:text-white"
            x-text="tab === 'teacher' ? '{{ __('Teacher Login') }}' : '{{ __('School Login') }}'">
            {{ $initialTab === 'school' ? __('School Login') : __('Teacher Login') }}
        </h2>

        <div dir="ltr" class="relative mx-auto mb-8 flex rounded-full bg-gray-900/60 p-1 ring-1 ring-white/10">
            <span class="absolute inset-y-1 left-1 w-[calc(50%-0.25rem)] rounded-full bg-indigo-600 shadow transition-transform duration-300 ease-out"
                  :class="tab === 'school' ? 'translate-x-full' : 'translate-x-0'"></span>

            <button type="button" @click="tab = 'teacher'"
                    class="relative z-10 flex-1 rounded-full py-2.5 text-sm font-semibold transition-colors duration-300"
                    :class="tab === 'teacher' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                {{ __('Teacher') }}
            </button>
            <button type="button" @click="tab = 'school'"
                    class="relative z-10 flex-1 rounded-full py-2.5 text-sm font-semibold transition-colors duration-300"
                    :class="tab === 'school' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                {{ __('School') }}
            </button>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <div x-ref="stage" class="relative overflow-hidden transition-[height] duration-300 ease-in-out">
            <div x-ref="teacherPanel"
                 class="transition-opacity duration-200 ease-in-out"
                 :class="tab === 'teacher' ? 'relative opacity-100' : 'absolute inset-x-0 top-0 pointer-events-none opacity-0'"
                 :aria-hidden="tab !== 'teacher'"
                 :inert="tab !== 'teacher'">
                <form method="POST" action="{{ route('teacher.login.store') }}">
                    @csrf
                    <input type="hidden" name="tab" value="teacher">

                    <div>
                        <x-input-label for="teacher_email" :value="__('Email')" />
                        <x-text-input id="teacher_email" class="block mt-1 w-full" type="email" name="email" :value="old('tab') === 'teacher' ? old('email') : null" autocomplete="username" />
                        @if ($initialTab === 'teacher')
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        @endif
                    </div>

                    <div class="mt-4">
                        <x-input-label for="teacher_password" :value="__('Password')" />
                        <x-text-input id="teacher_password" class="block mt-1 w-full" type="password" name="password" autocomplete="current-password" />
                        @if ($initialTab === 'teacher')
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        @endif
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <x-primary-button class="w-full justify-center">
                            {{ __('Log in') }}
                        </x-primary-button>
                    </div>
                </form>

                <div class="mt-6 border-t border-gray-600 pt-6 text-center text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400">
                    {{ __('New teacher?') }}
                    <a href="{{ route('teacher.register') }}" class="font-bold text-gray-600 underline dark:text-gray-200">
                        {{ __('Register') }}
                    </a>
                </div>
            </div>

            <div x-ref="schoolPanel"
                 class="transition-opacity duration-200 ease-in-out"
                 :class="tab === 'school' ? 'relative opacity-100' : 'absolute inset-x-0 top-0 pointer-events-none opacity-0'"
                 :aria-hidden="tab !== 'school'"
                 :inert="tab !== 'school'">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="tab" value="school">

                    <div>
                        <x-input-label for="school_email" :value="__('Email')" />
                        <x-text-input id="school_email" class="block mt-1 w-full" type="email" name="email" :value="old('tab') === 'school' ? old('email') : null" autocomplete="username" />
                        @if ($initialTab === 'school')
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        @endif
                    </div>

                    <div class="mt-4">
                        <x-input-label for="school_password" :value="__('Password')" />
                        <x-text-input id="school_password" class="block mt-1 w-full" type="password" name="password" autocomplete="current-password" />
                        @if ($initialTab === 'school')
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        @endif
                    </div>

                    <div class="block mt-4">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        @if (Route::has('password.request'))
                            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif

                        <x-primary-button>
                            {{ __('Log in') }}
                        </x-primary-button>
                    </div>
                </form>

                <div class="mt-6 border-t border-gray-600 pt-6 text-center text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400">
                    {{ __('New school?') }}
                    <a href="{{ route('register') }}" class="font-bold text-gray-600 underline dark:text-gray-200">
                        {{ __('Register') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
