<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GeneralSettingsRequest;
use App\Models\SiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', [
            'settings' => SiteSetting::getMany(['site_name', 'allow_registration', 'allow_applications', 'require_admin_approval']),
        ]);
    }

    public function update(GeneralSettingsRequest $request): RedirectResponse
    {
        SiteSetting::setMany([
            'site_name' => $request->validated('site_name'),
            'allow_registration' => $request->boolean('allow_registration'),
            'allow_applications' => $request->boolean('allow_applications'),
            'require_admin_approval' => $request->boolean('require_admin_approval'),
        ]);

        ActivityLogger::log('admin.settings_updated', 'Admin updated general site settings');

        return back()->with('status', 'Settings updated.');
    }
}
