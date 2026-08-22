<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\School\UpdateBoothRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BoothController extends Controller
{
    public function edit(): RedirectResponse
    {
        return redirect()->route('school.dashboard');
    }

    public function update(UpdateBoothRequest $request): RedirectResponse
    {
        $school = $request->user()->school;
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($school->logo_path) {
                Storage::disk('public')->delete($school->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $school->update($data);

        ActivityLogger::log(
            'school.profile_updated',
            "{$school->name} updated their booth profile",
            $school,
            [],
            route('admin.schools.show', $school)
        );

        if ($request->hasFile('logo')) {
            ActivityLogger::log('school.logo_changed', "{$school->name} changed their logo", $school);
        }

        return redirect()->route('school.dashboard')->with('status', 'Booth updated successfully.');
    }

    public function publish(Request $request): RedirectResponse
    {
        $school = $request->user()->school;

        $school->update([
            'is_published' => true,
            'approved_at' => $school->approved_at ?? now(),
        ]);

        ActivityLogger::log(
            'school.published',
            "{$school->name} published their booth",
            $school,
            [],
            route('admin.schools.show', $school)
        );

        return redirect()->route('school.dashboard')->with('status', 'Your booth is now published.');
    }

    public function unpublish(Request $request): RedirectResponse
    {
        $school = $request->user()->school;
        $school->update(['is_published' => false]);

        ActivityLogger::log('school.unpublished', "{$school->name} unpublished their booth", $school);

        return redirect()->route('school.dashboard')->with('status', 'Your booth has been unpublished.');
    }
}
