<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GeneralSettingsRequest;
use App\Models\SiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function edit(): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }

    public function update(GeneralSettingsRequest $request): RedirectResponse
    {
        $data = [
            'site_name' => $request->validated('site_name'),
            'site_name_ar' => $request->validated('site_name_ar'),
            'support_whatsapp' => $request->validated('support_whatsapp'),
            'allow_registration' => $request->boolean('allow_registration'),
            'allow_applications' => $request->boolean('allow_applications'),
            'require_admin_approval' => $request->boolean('require_admin_approval'),
            'show_site_name_in_nav' => $request->boolean('show_site_name_in_nav'),
            'nav_logo_height' => $request->validated('nav_logo_height'),
            'expo_starts_at' => $request->validated('expo_starts_at'),
            'expo_days' => $request->validated('expo_days') ?: 3,
            'dev_mode_enabled' => $request->boolean('dev_mode_enabled'),
            'dev_mode_message' => $request->validated('dev_mode_message'),
            'dev_mode_ends_at' => $request->validated('dev_mode_ends_at'),
        ];

        if ($request->hasFile('link_preview_image')) {
            $existing = SiteSetting::get('link_preview_image_path');
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }
            $data['link_preview_image_path'] = $request->file('link_preview_image')->store('site', 'public');
        }

        if ($request->hasFile('expo_logo')) {
            $existing = SiteSetting::get('expo_logo_path');
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }
            $data['expo_logo_path'] = $request->file('expo_logo')->store('site', 'public');
        }

        $wasDevMode = SiteSetting::get('dev_mode_enabled', false);

        SiteSetting::setMany($data);

        if ($data['dev_mode_enabled'] && ! $wasDevMode) {
            ActivityLogger::log('admin.dev_mode_enabled', 'Admin enabled Dev Mode'.($data['dev_mode_ends_at'] ? ' until '.$data['dev_mode_ends_at'] : ''));
        } elseif (! $data['dev_mode_enabled'] && $wasDevMode) {
            ActivityLogger::log('admin.dev_mode_disabled', 'Admin disabled Dev Mode');
        }

        ActivityLogger::log('admin.settings_updated', 'Admin updated general site settings');

        return back()->with('status', 'Settings updated.');
    }

    public function regenerateGuestLink(): RedirectResponse
    {
        SiteSetting::set('guest_link_token', Str::random(32));

        ActivityLogger::log('admin.guest_link_regenerated', 'Admin regenerated the guest access link');

        return back()->with('status', 'Guest link regenerated. The previous link no longer works.');
    }
}
