<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTeacherPasswordRequest;
use App\Models\Teacher;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $query = Teacher::query()->with('user');

        if ($search = $request->get('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return view('admin.teachers.index', [
            'teachers' => $query->latest()->paginate(15)->withQueryString(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function edit(Teacher $teacher): View
    {
        return view('admin.teachers.edit', [
            'teacher' => $teacher,
        ]);
    }

    public function updatePassword(UpdateTeacherPasswordRequest $request, Teacher $teacher): RedirectResponse
    {
        $teacher->user->update(['password' => Hash::make($request->validated('password'))]);

        ActivityLogger::log('admin.teacher_password_changed', "Admin changed the password for {$teacher->user->name}");

        return redirect()->route('admin.teachers.edit', $teacher)->with('status', 'Password updated.');
    }

    public function suspend(Teacher $teacher): RedirectResponse
    {
        $teacher->update(['status' => 'suspended']);

        ActivityLogger::log('teacher.suspended', "Admin suspended {$teacher->user->name}");

        return back()->with('status', 'Teacher suspended.');
    }

    public function reactivate(Teacher $teacher): RedirectResponse
    {
        $teacher->update(['status' => 'active']);

        ActivityLogger::log('teacher.reactivated', "Admin reactivated {$teacher->user->name}");

        return back()->with('status', 'Teacher reactivated.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $name = $teacher->user->name;
        $teacher->user()->delete();

        ActivityLogger::log('admin.teacher_deleted', "Admin deleted teacher: {$name}");

        return redirect()->route('admin.teachers.index')->with('status', 'Teacher deleted.');
    }
}
