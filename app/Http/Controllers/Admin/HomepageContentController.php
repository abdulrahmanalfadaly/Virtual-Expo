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
        'hero_headline', 'hero_headline_ar',
        'hero_description', 'hero_description_ar',
        'about_content', 'about_content_ar',
        'contact_email', 'contact_phone', 'contact_address', 'contact_address_ar',
        'support_info', 'support_info_ar',
        'footer_text', 'footer_text_ar', 'site_background_path',
        'hero_overlay_opacity',
        'schools_heading_prefix', 'schools_heading_prefix_ar',
        'schools_heading_highlight', 'schools_heading_highlight_ar',
    ];

    public function edit(): View
    {
        return view('admin.homepage-content', [
            'settings' => SiteSetting::getMany(self::KEYS),
        ]);
    }

    public function update(HomepageContentRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['site_background']);

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
