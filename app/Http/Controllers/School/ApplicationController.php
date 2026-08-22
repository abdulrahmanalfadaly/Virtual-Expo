<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('school.dashboard');
    }

    public function downloadCv(Application $application): StreamedResponse
    {
        $this->authorize('view', $application);

        if (! $application->viewed_at) {
            $application->update(['viewed_at' => now()]);
        }

        return Storage::disk('local')->download($application->cv_path, $application->cv_original_name);
    }
}
