@props(['school', 'allowApplications' => true, 'modalOpacity' => 90])

@php
    $embedUrl = \App\Support\VideoUrl::embedUrl($school->video_url);
    $initials = \Illuminate\Support\Str::of($school->name)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp

<div id="school-modal-{{ $school->id }}" class="booth-modal fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-950/75 modal-backdrop"></div>

    <div class="relative z-10 flex h-full items-center justify-center p-0 sm:p-4">
        <div class="modal-panel relative flex h-full w-full flex-col overflow-y-auto scrollbar-thin shadow-2xl backdrop-blur-sm sm:h-auto sm:max-h-[92vh] sm:max-w-3xl sm:rounded-2xl"
            style="--modal-alpha: {{ $modalOpacity / 100 }};">
            <button type="button" class="modal-close absolute right-4 top-4 z-20 rounded-full bg-white/90 p-2 text-gray-700 shadow hover:bg-white" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                </svg>
            </button>

            <div class="flex items-center gap-5 border-b border-gray-100 p-6 pr-16 dark:border-gray-800 sm:p-8 sm:pr-20">
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
                        <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">About</h4>
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
                    @if ($allowApplications)
                        <button type="button" class="apply-trigger inline-flex items-center justify-center gap-2 rounded-full border-2 border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                            Submit Your CV
                        </button>
                    @endif

                    @if ($school->zoom_url)
                        <a href="{{ $school->zoom_url }}" target="_blank" rel="noopener"
                            class="inline-flex flex-1 items-center justify-center gap-3 rounded-full px-8 py-4 text-base font-bold text-white shadow-lg shadow-indigo-500/30 transition hover:scale-[1.02] hover:opacity-95"
                            style="background-color: {{ $school->theme_color }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M4 5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-3.382l4.447 2.223A1 1 0 0022 13v-6a1 1 0 00-1.553-.832L16 8.382V5a2 2 0 00-2-2H4a2 2 0 00-2 2z" />
                            </svg>
                            Join Zoom Meeting
                        </a>
                    @endif
                </div>
            </div>

            @if ($allowApplications)
                <div class="apply-panel border-t border-gray-200 px-6 py-8 dark:border-gray-700 sm:px-10">
                    <h4 class="font-display text-lg font-semibold text-gray-900 dark:text-white">Apply to {{ $school->name }}</h4>
                    <form class="apply-form mt-4 space-y-4" data-apply-url="{{ route('apply.store', $school) }}">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full name</label>
                                <input type="text" name="applicant_name" required class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                <p class="field-error mt-1 text-xs text-red-600" data-field="applicant_name"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input type="email" name="applicant_email" required class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                <p class="field-error mt-1 text-xs text-red-600" data-field="applicant_email"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone (optional)</label>
                                <input type="text" name="applicant_phone" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                <p class="field-error mt-1 text-xs text-red-600" data-field="applicant_phone"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">CV (PDF/DOC/DOCX, max 5MB)</label>
                                <input type="file" name="cv" required accept=".pdf,.doc,.docx" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                <p class="field-error mt-1 text-xs text-red-600" data-field="cv"></p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message (optional)</label>
                            <textarea name="message" rows="3" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"></textarea>
                            <p class="field-error mt-1 text-xs text-red-600" data-field="message"></p>
                        </div>
                        <div class="apply-feedback text-sm"></div>
                        <button type="submit" class="apply-submit inline-flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold text-white shadow transition hover:opacity-90"
                            style="background-color: {{ $school->theme_color }}">
                            Submit Application
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
