<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BoothSettingsRequest;
use App\Models\SiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BoothSettingsController extends Controller
{
    private const KEYS = [
        'booth_template_path', 'booth_logo_x',
        'booth_logo_y', 'booth_logo_width', 'booth_logo_max_height',
        'booth_name_curve', 'booth_name_x', 'booth_name_y',
        'booth_grid_columns', 'booth_grid_gap', 'booth_modal_opacity',
    ];

    public function edit(): View
    {
        return view('admin.booth-settings', [
            'settings' => SiteSetting::getMany(self::KEYS),
        ]);
    }

    public function update(BoothSettingsRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('booth_template');

        if ($request->hasFile('booth_template')) {
            $existing = SiteSetting::get('booth_template_path');
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }
            $data['booth_template_path'] = $request->file('booth_template')->store('site', 'public');
        }

        SiteSetting::setMany($data);

        ActivityLogger::log('admin.booth_settings_updated', 'Admin updated booth template settings');

        return back()->with('status', 'Booth settings updated.');
    }
}
