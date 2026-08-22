<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\School;
use App\Models\SiteSetting;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalSchools' => School::count(),
            'publishedSchools' => School::where('is_published', true)->where('status', 'active')->count(),
            'unpublishedSchools' => School::where('is_published', false)->count(),
            'suspendedSchools' => School::where('status', 'suspended')->count(),
            'totalApplications' => Application::count(),
            'recentSchools' => School::latest()->take(5)->get(),
            'recentActivity' => ActivityLog::with(['user', 'school'])->latest()->take(10)->get(),
            'settings' => SiteSetting::getMany(['site_name', 'allow_registration', 'allow_applications', 'require_admin_approval']),
        ]);
    }
}
