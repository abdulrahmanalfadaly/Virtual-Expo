@props(['school', 'allowApplications' => true, 'modalOpacity' => 90, 'appliedAt' => null])

@php
    $embedUrl = \App\Support\VideoUrl::embedUrl($school->video_url);
    $initials = \Illuminate\Support\Str::of($school->name)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp

<div id="school-modal-{{ $school->id }}" class="booth-modal fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-950/75 modal-backdrop"></div>

    <div class="relative z-10 flex h-full items-center justify-center p-0 sm:p-4">
        <div class="modal-panel relative flex h-full w-full flex-col overflow-y-auto scrollbar-thin shadow-2xl backdrop-blur-sm sm:h-auto sm:max-h-[92vh] sm:max-w-3xl sm:rounded-2xl"
            style="--modal-alpha: {{ $modalOpacity / 100 }};">
            <button type="button" class="modal-close absolute end-4 top-4 z-20 rounded-full bg-white/90 p-2 text-gray-700 shadow hover:bg-white" aria-label="{{ __('Close') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                </svg>
            </button>

            <div class="flex items-center gap-5 border-b border-gray-100 p-6 pe-16 dark:border-gray-800 sm:p-8 sm:pe-20">
                <div class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gray-50 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700 sm:h-36 sm:w-36">
                    @if ($school->logo_path)
                        <img src="{{ $school->logoUrl() }}" alt="{{ $school->name }} logo" class="h-full w-full object-contain p-2">
                    @else
                        <span class="text-2xl font-bold text-gray-400">{{ $initials }}</span>
                    @endif
                </div>
                <div>
                    <h3 class="font-display text-2xl font-semibold text-gray-900 dark:text-white sm:text-4xl">{{ $school->name }}</h3>
                    @if ($school->school_type)
                        <span class="mt-2 inline-block rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                            {{ $school->schoolTypeLabel() }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="px-6 pt-8 pb-12 sm:px-10 sm:pt-10 sm:pb-16">
                @if ($school->full_description)
                    <div>
                        <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('About') }}</h4>
                        <p class="mt-2 whitespace-pre-line text-gray-700 dark:text-gray-300">{{ $school->full_description }}</p>
                    </div>
                @endif

                @if ($embedUrl)
                    <div class="mt-12">
                        <div class="aspect-video overflow-hidden rounded-xl bg-black">
                            <iframe src="{{ $embedUrl }}" class="h-full w-full" loading="lazy" allowfullscreen title="{{ $school->name }} video"></iframe>
                        </div>
                    </div>
                @endif

                <div class="mt-12 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
                    @if ($allowApplications && auth()->user()?->isTeacher())
                        <button type="button" class="apply-trigger inline-flex items-center justify-center gap-2 rounded-full border-2 px-6 py-3 text-sm font-semibold transition {{ $appliedAt ? 'border-emerald-300 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-600 dark:text-emerald-300 dark:hover:bg-emerald-900/20' : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800' }}">
                            @if ($appliedAt)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                {{ __('Applied — Update CV') }}
                            @else
                                {{ __('Submit Your CV') }}
                            @endif
                        </button>
                    @endif

                    @if ($school->zoom_url)
                        <a href="{{ $school->zoom_url }}" target="_blank" rel="noopener"
                            class="inline-flex flex-1 items-center justify-center gap-3 rounded-full px-8 py-4 text-base font-bold text-white shadow-lg shadow-indigo-500/30 transition hover:scale-[1.02] hover:opacity-95"
                            style="background-color: {{ $school->theme_color }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M4 5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-3.382l4.447 2.223A1 1 0 0022 13v-6a1 1 0 00-1.553-.832L16 8.382V5a2 2 0 00-2-2H4a2 2 0 00-2 2z" />
                            </svg>
                            {{ __('Join Zoom Meeting') }}
                        </a>
                    @endif
                </div>
            </div>

            @if ($allowApplications && auth()->user()?->isTeacher())
                <div class="apply-panel border-t border-gray-200 bg-gray-50/60 px-6 py-8 dark:border-gray-700 dark:bg-gray-900/40 sm:px-10">
                    <div class="mx-auto max-w-xl">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" style="background-color: {{ $school->theme_color }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                            </span>
                            <div>
                                <h4 class="font-display text-lg font-semibold text-gray-900 dark:text-white">{{ __('Apply to :school', ['school' => $school->name]) }}</h4>
                                @auth
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('Applying as') }} <span class="font-medium text-gray-700 dark:text-gray-200">{{ auth()->user()->name }}</span> &middot; {{ auth()->user()->email }}
                                    </p>
                                @endauth
                            </div>
                        </div>

                        @if ($appliedAt)
                            <div class="mt-5 flex items-start gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-300 dark:ring-emerald-900/40">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span>{{ __('You already applied to this school on :date. Submitting again will replace your previous CV and message.', ['date' => $appliedAt->format('M j, Y')]) }}</span>
                            </div>
                        @endif

                        <form class="apply-form mt-6 space-y-5" data-apply-url="{{ route('apply.store', $school) }}"
                            data-text-upload-default="{{ __('Click to upload your CV') }}"
                            data-hint-upload-default="{{ __('PDF, DOC, or DOCX · max 5MB') }}"
                            data-hint-upload-ready="{{ __('Ready to submit — click to choose a different file') }}"
                            data-msg-fix-errors="{{ __('Please correct the errors above.') }}"
                            data-msg-generic-error="{{ __('Something went wrong. Please try again.') }}"
                            data-msg-network-error="{{ __('Network error. Please try again.') }}">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('CV / Resume') }}</label>
                                <label for="cv-input-{{ $school->id }}"
                                    class="cv-dropzone mt-2 flex cursor-pointer items-center gap-3 rounded-xl border-2 border-dashed border-gray-300 bg-white px-4 py-4 transition hover:border-indigo-400 hover:bg-indigo-50/40 dark:border-gray-600 dark:bg-gray-800 dark:hover:border-indigo-500 dark:hover:bg-indigo-500/10">
                                    <span class="cv-dropzone-icon flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                    </span>
                                    <span class="flex-1 overflow-hidden">
                                        <span class="cv-dropzone-text block truncate text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Click to upload your CV') }}</span>
                                        <span class="cv-dropzone-hint block text-xs text-gray-400">{{ __('PDF, DOC, or DOCX · max 5MB') }}</span>
                                    </span>
                                    <input id="cv-input-{{ $school->id }}" type="file" name="cv" required accept=".pdf,.doc,.docx" class="sr-only">
                                </label>
                                <p class="field-error mt-1 text-xs text-red-600" data-field="cv"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Message (optional)') }}</label>
                                <textarea name="message" rows="3" placeholder="{{ __('Tell them a bit about yourself...') }}" class="mt-2 w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"></textarea>
                                <p class="field-error mt-1 text-xs text-red-600" data-field="message"></p>
                            </div>

                            <div class="apply-feedback text-sm"></div>

                            <button type="submit" class="apply-submit inline-flex w-full items-center justify-center gap-2 rounded-full px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:opacity-90 sm:w-auto"
                                style="background-color: {{ $school->theme_color }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-6 6m6-6l6 6" />
                                </svg>
                                {{ $appliedAt ? __('Update Application') : __('Submit Application') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
