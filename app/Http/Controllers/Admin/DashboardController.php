<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\School;
use App\Models\SiteSetting;
use App\Models\Teacher;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $guestToken = SiteSetting::get('guest_link_token');

        if (! $guestToken) {
            $guestToken = Str::random(32);
            SiteSetting::set('guest_link_token', $guestToken);
        }

        return view('admin.dashboard', [
            'totalSchools' => School::count(),
            'publishedSchools' => School::where('is_published', true)->where('status', 'active')->count(),
            'unpublishedSchools' => School::where('is_published', false)->count(),
            'suspendedSchools' => School::where('status', 'suspended')->count(),
            'totalTeachers' => Teacher::count(),
            'totalApplications' => Application::count(),
            'recentSchools' => School::latest()->take(5)->get(),
            'recentActivity' => ActivityLog::with(['user', 'school'])->latest()->take(10)->get(),
            'settings' => SiteSetting::getMany(['site_name', 'site_name_ar', 'support_whatsapp', 'allow_registration', 'allow_applications', 'require_admin_approval', 'link_preview_image_path', 'expo_logo_path', 'show_site_name_in_nav', 'nav_logo_height', 'expo_starts_at', 'expo_days', 'dev_mode_enabled', 'dev_mode_message', 'dev_mode_ends_at']),
            'guestLink' => route('guest.enter', $guestToken),
        ]);
    }
}
