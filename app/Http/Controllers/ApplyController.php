<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyRequest;
use App\Models\School;
use App\Models\SiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ApplyController extends Controller
{
    public function store(ApplyRequest $request, School $school): JsonResponse
    {
        if (! $school->isVisible()) {
            abort(404);
        }

        if (! SiteSetting::get('allow_applications', true)) {
            return response()->json([
                'message' => 'Applications are currently closed.',
            ], 403);
        }

        $data = $request->validated();
        $teacher = $request->user()->teacher;

        $file = $request->file('cv');
        $storedName = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs("cvs/{$school->id}", $storedName, 'local');

        $application = $school->applications()->create([
            'teacher_id' => $teacher?->id,
            'applicant_name' => $request->user()->name,
            'applicant_email' => $request->user()->email,
            'applicant_phone' => $teacher?->phone,
            'message' => $data['message'] ?? null,
            'cv_path' => $path,
            'cv_original_name' => $file->getClientOriginalName(),
        ]);

        ActivityLogger::log(
            'application.submitted',
            "New application from {$application->applicant_name} for {$school->name}",
            $school,
            ['application_id' => $application->id],
            route('admin.applications.index', ['school' => $school->id])
        );

        return response()->json([
            'message' => 'Your application has been submitted successfully.',
        ]);
    }
}
