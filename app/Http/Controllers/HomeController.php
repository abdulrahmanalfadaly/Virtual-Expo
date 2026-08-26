<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $schools = School::query()
            ->where('is_published', true)
            ->where('status', 'active')
            ->with(['programs', 'galleryImages'])
            ->orderBy('name')
            ->get();

        $teacher = Auth::user()?->teacher;

        $existingApplications = $teacher
            ? $teacher->applications()->pluck('created_at', 'school_id')
            : collect();

        $localizedKeys = [
            'hero_headline', 'hero_description', 'about_content',
            'contact_address', 'support_info', 'footer_text', 'site_name',
            'schools_heading_prefix', 'schools_heading_highlight',
        ];

        $content = SiteSetting::getMany([
            'contact_email', 'contact_phone',
            'expo_logo_path', 'site_background_path',
            'hero_overlay_opacity', 'show_site_name_in_nav', 'nav_logo_height',
        ]);

        foreach ($localizedKeys as $key) {
            $content[$key] = SiteSetting::getLocalized($key);
        }

        return view('home', [
            'schools' => $schools,
            'existingApplications' => $existingApplications,
            'content' => $content,
            'boothSettings' => SiteSetting::getMany([
                'booth_template_path', 'booth_logo_x',
                'booth_logo_y', 'booth_logo_width', 'booth_logo_max_height',
                'booth_name_curve', 'booth_name_x', 'booth_name_y',
            ]),
            'boothGrid' => SiteSetting::getMany(['booth_grid_columns', 'booth_grid_gap']),
            'boothModalOpacity' => SiteSetting::get('booth_modal_opacity', 90),
            'allowApplications' => SiteSetting::get('allow_applications', true),
        ]);
    }
}
