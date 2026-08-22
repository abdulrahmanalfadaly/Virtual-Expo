<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Application::query()->with('school');

        if ($schoolId = $request->get('school')) {
            $query->where('school_id', $schoolId);
        }

        return view('admin.applications.index', [
            'applications' => $query->latest()->paginate(20)->withQueryString(),
            'schools' => School::orderBy('name')->get(['id', 'name']),
            'selectedSchool' => $schoolId,
        ]);
    }

    public function downloadCv(Application $application): StreamedResponse
    {
        if (! $application->viewed_at) {
            $application->update(['viewed_at' => now()]);
        }

        return Storage::disk('local')->download($application->cv_path, $application->cv_original_name);
    }
}
