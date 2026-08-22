@php
    $layout = auth()->user()->isAdmin() ? 'admin-layout' : 'school-layout';
@endphp
<x-dynamic-component :component="$layout" title="Account">
    <div class="mx-auto max-w-xl space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            @include('profile.partials.update-password-form')
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            @include('profile.partials.logout-form')
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-dynamic-component>
