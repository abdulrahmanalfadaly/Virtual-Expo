<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomepageContentRequest;
use App\Models\SiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomepageContentController extends Controller
{
    private const KEYS = [
        'hero_headline', 'hero_description', 'about_content',
        'contact_email', 'contact_phone', 'contact_address', 'support_info',
        'footer_text', 'expo_logo_path', 'site_background_path',
        'hero_overlay_opacity', 'schools_heading_prefix', 'schools_heading_highlight',
    ];

    public function edit(): View
    {
        return view('admin.homepage-content', [
            'settings' => SiteSetting::getMany(self::KEYS),
        ]);
    }

    public function update(HomepageContentRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['expo_logo', 'site_background']);

        if ($request->hasFile('expo_logo')) {
            $existing = SiteSetting::get('expo_logo_path');
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }
            $data['expo_logo_path'] = $request->file('expo_logo')->store('site', 'public');
        }

        if ($request->hasFile('site_background')) {
            $existing = SiteSetting::get('site_background_path');
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }
            $data['site_background_path'] = $request->file('site_background')->store('site', 'public');
        }

        SiteSetting::setMany($data);

        ActivityLogger::log('admin.homepage_updated', 'Admin updated homepage content');

        return back()->with('status', 'Homepage content updated.');
    }
}
