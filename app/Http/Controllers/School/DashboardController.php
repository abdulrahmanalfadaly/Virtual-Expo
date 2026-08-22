<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $school = $request->user()->school;

        return view('school.dashboard', [
            'school' => $school,
            'applicationsCount' => $school->applications()->count(),
            'boothSettings' => SiteSetting::getMany([
                'booth_template_path',
                'booth_logo_x',
                'booth_logo_y',
                'booth_logo_width',
                'booth_logo_max_height',
                'booth_name_curve',
                'booth_name_x',
                'booth_name_y',
            ]),
            'applications' => $school->applications()->latest()->paginate(15),
        ]);
    }
}
