<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GeneralSettingsRequest;
use App\Models\SiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

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
            'allow_registration' => $request->boolean('allow_registration'),
            'allow_applications' => $request->boolean('allow_applications'),
            'require_admin_approval' => $request->boolean('require_admin_approval'),
            'show_site_name_in_nav' => $request->boolean('show_site_name_in_nav'),
            'nav_logo_height' => $request->validated('nav_logo_height'),
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

        SiteSetting::setMany($data);

        ActivityLogger::log('admin.settings_updated', 'Admin updated general site settings');

        return back()->with('status', 'Settings updated.');
    }
}
