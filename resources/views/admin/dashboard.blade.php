<x-admin-layout title="Dashboard">
    <div class="mb-8">
        <h1 class="font-display text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">Overview</h1>
        <p class="mt-1 text-sm text-gray-500">A snapshot of your Virtual School Expo, right now.</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1m-6 4h1m4 0h1m-6 4h1m4 0h1" />
                </svg>
            </span>
            <p class="mt-4 text-sm text-gray-500">Total Schools</p>
            <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalSchools }}</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25L15 10.5m6 1.5a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <p class="mt-4 text-sm text-gray-500">Published</p>
            <p class="mt-1 text-3xl font-semibold text-emerald-600">{{ $publishedSchools }}</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 9v6m6-6v6M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <p class="mt-4 text-sm text-gray-500">Unpublished</p>
            <p class="mt-1 text-3xl font-semibold text-amber-600">{{ $unpublishedSchools }}</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 105.636 5.636a9 9 0 0012.728 12.728zM5.636 5.636l12.728 12.728" />
                </svg>
            </span>
            <p class="mt-4 text-sm text-gray-500">Suspended</p>
            <p class="mt-1 text-3xl font-semibold text-red-600">{{ $suspendedSchools }}</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347M4.26 10.147a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814M4.26 10.147A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                </svg>
            </span>
            <p class="mt-4 text-sm text-gray-500">Teachers</p>
            <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalTeachers }}</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </span>
            <p class="mt-4 text-sm text-gray-500">Applications</p>
            <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalApplications }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 font-display text-lg font-semibold text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    Recent Registrations
                </h2>
                <a href="{{ route('admin.schools.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">View all &rarr;</a>
            </div>
            <div class="mt-5 divide-y divide-gray-50">
                @forelse ($recentSchools as $school)
                    @php $initials = \Illuminate\Support\Str::of($school->name)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode(''); @endphp
                    <a href="{{ route('admin.schools.show', $school) }}" class="-mx-2 flex items-center justify-between gap-3 rounded-lg px-2 py-3 transition hover:bg-gray-50">
                        <span class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-xs font-semibold uppercase text-indigo-600">{{ $initials }}</span>
                            <span class="text-sm font-medium text-gray-800">{{ $school->name }}</span>
                        </span>
                        <span class="shrink-0 text-xs text-gray-400">{{ $school->created_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">No schools yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 font-display text-lg font-semibold text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Recent Activity
                </h2>
                <a href="{{ route('admin.activity.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">View all &rarr;</a>
            </div>
            <div class="mt-5 space-y-4">
                @forelse ($recentActivity as $log)
                    @if ($log->action === 'school.password_reset_requested' && $resetRequest = $log->passwordResetRequest())
                        @include('admin.partials.password-reset-request-card', ['log' => $log, 'resetRequest' => $resetRequest])
                    @else
                        <div class="relative flex gap-3 pl-1">
                            <span class="relative mt-1.5 flex h-2 w-2 shrink-0 rounded-full bg-indigo-400"></span>
                            <div>
                                <p class="text-sm text-gray-800">{{ $log->description }}</p>
                                <p class="mt-0.5 text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">No activity yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-10">
        <div class="mb-5">
            <h2 class="flex items-center gap-2 font-display text-xl font-semibold text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0H12" />
                </svg>
                General Settings
            </h2>
            <p class="mt-1 text-sm text-gray-500">Core configuration for how your expo presents itself.</p>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <h3 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z" />
                        </svg>
                        Branding
                    </h3>

                    <div class="mt-5 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Site / Expo Name</label>
                            <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name']) }}" required class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="flex items-center justify-between gap-4 border-t border-gray-100 pt-5">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Show Site Name Next to Logo</p>
                                <p class="text-xs text-gray-500">When off, only the logo appears in the navigation.</p>
                            </div>
                            <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                                <input type="checkbox" name="show_site_name_in_nav" value="1" @checked(old('show_site_name_in_nav', $settings['show_site_name_in_nav'] ?? true)) class="peer sr-only">
                                <div class="h-6 w-11 rounded-full bg-gray-200 transition-colors peer-checked:bg-indigo-600"></div>
                                <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                            </label>
                        </div>

                        <div class="border-t border-gray-100 pt-5">
                            <label class="block text-sm font-medium text-gray-700">Company / Expo Logo</label>
                            <p class="text-xs text-gray-400">Shown next to the site name in the navigation on every page.</p>
                            @if (! empty($settings['expo_logo_path']))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['expo_logo_path']) }}" style="height: {{ $settings['nav_logo_height'] ?? 48 }}px;" class="mt-2 w-auto rounded-lg object-contain ring-1 ring-gray-200">
                            @endif
                            <input type="file" name="expo_logo" accept="image/*" class="mt-2 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Logo Height in Navigation (px)</label>
                            <p class="text-xs text-gray-400">No size limit — width scales automatically to match the logo's shape.</p>
                            <input type="number" name="nav_logo_height" value="{{ old('nav_logo_height', $settings['nav_logo_height'] ?? 48) }}" min="1" required class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                        <h3 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                            </svg>
                            Social Sharing
                        </h3>
                        <div class="mt-5">
                            <label class="block text-sm font-medium text-gray-700">Link Preview Image</label>
                            <p class="text-xs text-gray-400">Shown when your link is shared on WhatsApp, iMessage, social media, etc. Recommended: 1200×630.</p>
                            @if (! empty($settings['link_preview_image_path']))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['link_preview_image_path']) }}" class="mt-2 h-24 w-full max-w-xs rounded-lg object-cover ring-1 ring-gray-200">
                            @endif
                            <input type="file" name="link_preview_image" accept="image/*" class="mt-2 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                        <h3 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            Access Control
                        </h3>
                        <div class="mt-5 space-y-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Allow New School Registration</p>
                                    <p class="text-xs text-gray-500">When off, the public registration form is closed.</p>
                                </div>
                                <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                                    <input type="checkbox" name="allow_registration" value="1" @checked($settings['allow_registration']) class="peer sr-only">
                                    <div class="h-6 w-11 rounded-full bg-gray-200 transition-colors peer-checked:bg-indigo-600"></div>
                                    <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between gap-4 border-t border-gray-100 pt-5">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Allow Applications</p>
                                    <p class="text-xs text-gray-500">When off, visitors cannot submit new CV applications.</p>
                                </div>
                                <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                                    <input type="checkbox" name="allow_applications" value="1" @checked($settings['allow_applications']) class="peer sr-only">
                                    <div class="h-6 w-11 rounded-full bg-gray-200 transition-colors peer-checked:bg-indigo-600"></div>
                                    <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between gap-4 border-t border-gray-100 pt-5">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Require Admin Approval</p>
                                    <p class="text-xs text-gray-500">New schools stay unpublished until manually approved.</p>
                                </div>
                                <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                                    <input type="checkbox" name="require_admin_approval" value="1" @checked($settings['require_admin_approval']) class="peer sr-only">
                                    <div class="h-6 w-11 rounded-full bg-gray-200 transition-colors peer-checked:bg-indigo-600"></div>
                                    <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $devModeEndsAt = old('dev_mode_ends_at', $settings['dev_mode_ends_at'] ? \Illuminate\Support\Carbon::parse($settings['dev_mode_ends_at'])->format('Y-m-d\TH:i') : '');
            @endphp
            <div class="rounded-2xl border-2 border-amber-200 bg-amber-50/40 p-6 shadow-sm">
                <h3 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    Maintenance / Dev Mode
                </h3>
                <p class="mt-1 text-xs text-amber-700/70">Takes the entire public site offline for everyone except admins.</p>

                <div class="mt-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Enable Dev Mode</p>
                        <p class="text-xs text-gray-500">Teachers, schools, and visitors will see a maintenance page instead of the site. Admins are unaffected.</p>
                    </div>
                    <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                        <input type="checkbox" name="dev_mode_enabled" value="1" @checked(old('dev_mode_enabled', $settings['dev_mode_enabled'] ?? false)) class="peer sr-only">
                        <div class="h-6 w-11 rounded-full bg-gray-200 transition-colors peer-checked:bg-amber-500"></div>
                        <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                    </label>
                </div>

                <div class="mt-5 border-t border-amber-100 pt-5">
                    <label class="block text-sm font-medium text-gray-700">Custom Message (optional)</label>
                    <p class="text-xs text-gray-400">Shown to visitors on the maintenance page. Leave blank to use the default message.</p>
                    <textarea name="dev_mode_message" rows="3" placeholder="We're making some updates behind the scenes. Please check back soon." class="mt-2 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('dev_mode_message', $settings['dev_mode_message']) }}</textarea>
                </div>

                <div class="mt-5 border-t border-amber-100 pt-5">
                    <label class="block text-sm font-medium text-gray-700">Auto-disable At (optional)</label>
                    <p class="text-xs text-gray-400">Dev Mode turns itself off automatically once this time passes. Leave blank to keep it on until you disable it manually.</p>
                    <input type="datetime-local" name="dev_mode_ends_at" value="{{ $devModeEndsAt }}" class="mt-2 w-full max-w-xs rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex justify-end">
                <button class="rounded-full bg-indigo-600 px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">Save Settings</button>
            </div>
        </form>
    </div>
</x-admin-layout>
