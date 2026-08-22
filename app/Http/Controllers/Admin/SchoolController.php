<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateSchoolRequest;
use App\Http\Requests\Admin\UpdateSchoolRequest;
use App\Models\School;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SchoolController extends Controller
{
    public function index(Request $request): View
    {
        $query = School::query()->with('user');

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            match ($status) {
                'published' => $query->where('is_published', true)->where('status', 'active'),
                'unpublished' => $query->where('is_published', false)->where('status', 'active'),
                'suspended' => $query->where('status', 'suspended'),
                default => null,
            };
        }

        return view('admin.schools.index', [
            'schools' => $query->latest()->paginate(15)->withQueryString(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function show(School $school): View
    {
        $school->load(['programs', 'galleryImages', 'applications']);

        return view('admin.schools.show', [
            'school' => $school,
            'boothSettings' => SiteSetting::getMany([
                'booth_template_path', 'booth_logo_x', 'booth_logo_y',
                'booth_logo_width', 'booth_logo_max_height', 'booth_name_curve',
                'booth_name_x', 'booth_name_y',
            ]),
        ]);
    }

    public function create(): View
    {
        return view('admin.schools.create');
    }

    public function store(CreateSchoolRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $school = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['contact_person'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'school',
            ]);

            return School::create([
                'user_id' => $user->id,
                'name' => $data['school_name'],
                'slug' => School::uniqueSlug($data['school_name']),
                'contact_person' => $data['contact_person'],
                'contact_email' => $data['email'],
            ]);
        });

        ActivityLogger::log(
            'admin.school_created',
            "Admin created school: {$school->name}",
            $school,
            [],
            route('admin.schools.show', $school)
        );

        return redirect()->route('admin.schools.edit', $school)->with('status', 'School created.');
    }

    public function edit(School $school): View
    {
        return view('admin.schools.edit', [
            'school' => $school,
            'boothSettings' => SiteSetting::getMany([
                'booth_template_path', 'booth_logo_x', 'booth_logo_y',
                'booth_logo_width', 'booth_logo_max_height', 'booth_name_curve',
                'booth_name_x', 'booth_name_y',
            ]),
        ]);
    }

    public function update(UpdateSchoolRequest $request, School $school): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($school->logo_path) {
                Storage::disk('public')->delete($school->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
            unset($data['logo']);
        }

        $school->update($data);

        ActivityLogger::log('admin.school_updated', "Admin updated {$school->name}", $school);

        return redirect()->route('admin.schools.edit', $school)->with('status', 'School updated.');
    }

    public function publish(School $school): RedirectResponse
    {
        $school->update([
            'is_published' => true,
            'approved_at' => $school->approved_at ?? now(),
        ]);
        ActivityLogger::log('admin.school_published', "Admin published {$school->name}", $school);

        return back()->with('status', 'School published.');
    }

    public function unpublish(School $school): RedirectResponse
    {
        $school->update(['is_published' => false]);
        ActivityLogger::log('admin.school_unpublished', "Admin unpublished {$school->name}", $school);

        return back()->with('status', 'School unpublished.');
    }

    public function suspend(School $school): RedirectResponse
    {
        $school->update(['status' => 'suspended']);
        ActivityLogger::log('school.suspended', "Admin suspended {$school->name}", $school);

        return back()->with('status', 'School suspended.');
    }

    public function reactivate(School $school): RedirectResponse
    {
        $school->update(['status' => 'active']);
        ActivityLogger::log('school.reactivated', "Admin reactivated {$school->name}", $school);

        return back()->with('status', 'School reactivated.');
    }

    public function destroy(School $school): RedirectResponse
    {
        foreach ($school->galleryImages as $image) {
            Storage::disk('public')->delete($image->path);
        }

        foreach ($school->applications as $application) {
            Storage::disk('local')->delete($application->cv_path);
        }

        if ($school->logo_path) {
            Storage::disk('public')->delete($school->logo_path);
        }

        $name = $school->name;
        $school->user()->delete();

        ActivityLogger::log('admin.school_deleted', "Admin deleted school: {$name}");

        return redirect()->route('admin.schools.index')->with('status', 'School deleted.');
    }
}
