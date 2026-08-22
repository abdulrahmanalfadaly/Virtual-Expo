<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\School\ProgramRequest;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(Request $request): View
    {
        $school = $request->user()->school;

        return view('school.programs.index', [
            'school' => $school,
            'programs' => $school->programs,
        ]);
    }

    public function store(ProgramRequest $request): RedirectResponse
    {
        $school = $request->user()->school;

        $school->programs()->create([
            ...$request->validated(),
            'sort_order' => $school->programs()->count(),
        ]);

        return back()->with('status', 'Program added.');
    }

    public function update(ProgramRequest $request, Program $program): RedirectResponse
    {
        $this->authorize('update', $program);

        $program->update($request->validated());

        return back()->with('status', 'Program updated.');
    }

    public function destroy(Request $request, Program $program): RedirectResponse
    {
        $this->authorize('delete', $program);

        $program->delete();

        return back()->with('status', 'Program removed.');
    }
}
