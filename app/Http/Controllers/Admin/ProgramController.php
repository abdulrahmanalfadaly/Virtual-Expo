<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\School\ProgramRequest;
use App\Models\Program;
use App\Models\School;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(School $school): View
    {
        return view('admin.schools.programs', [
            'school' => $school,
            'programs' => $school->programs,
        ]);
    }

    public function store(ProgramRequest $request, School $school): RedirectResponse
    {
        $school->programs()->create([
            ...$request->validated(),
            'sort_order' => $school->programs()->count(),
        ]);

        ActivityLogger::log('admin.program_created', "Admin added a program for {$school->name}", $school);

        return back()->with('status', 'Program added.');
    }

    public function update(ProgramRequest $request, School $school, Program $program): RedirectResponse
    {
        abort_if($program->school_id !== $school->id, 404);

        $program->update($request->validated());

        ActivityLogger::log('admin.program_updated', "Admin updated a program for {$school->name}", $school);

        return back()->with('status', 'Program updated.');
    }

    public function destroy(School $school, Program $program): RedirectResponse
    {
        abort_if($program->school_id !== $school->id, 404);

        $program->delete();

        ActivityLogger::log('admin.program_deleted', "Admin removed a program for {$school->name}", $school);

        return back()->with('status', 'Program removed.');
    }
}
