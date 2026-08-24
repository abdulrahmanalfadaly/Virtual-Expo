<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BoothSettingsController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GalleryImageController as AdminGalleryImageController;
use App\Http\Controllers\Admin\HomepageContentController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PasswordResetRequestController;
use App\Http\Controllers\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\Admin\SchoolController as AdminSchoolController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TeacherController as AdminTeacherController;
use App\Http\Controllers\ApplyController;
use App\Http\Controllers\Auth\TeacherAuthController;
use App\Http\Controllers\Auth\TeacherRegisteredController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\School\ApplicationController as SchoolApplicationController;
use App\Http\Controllers\School\BoothController;
use App\Http\Controllers\School\DashboardController as SchoolDashboardController;
use App\Http\Controllers\School\GalleryImageController;
use App\Http\Controllers\School\ProgramController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->middleware(['auth', 'school.active', 'teacher.active'])
    ->name('home');

Route::post('/apply/{school:slug}', [ApplyController::class, 'store'])
    ->middleware(['auth', 'role:teacher', 'teacher.active'])
    ->name('apply.store');

// Teacher registration & login (guest only)
Route::middleware('guest')->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/register', [TeacherRegisteredController::class, 'create'])->name('register');
    Route::post('/register', [TeacherRegisteredController::class, 'store'])->name('register.store');
    Route::get('/login', [TeacherAuthController::class, 'create'])->name('login');
    Route::post('/login', [TeacherAuthController::class, 'store'])->name('login.store');
});

Route::get('/dashboard', function () {
    return auth()->user()->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('school.dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// School dashboard
Route::middleware(['auth', 'role:school', 'school.active'])->prefix('school')->name('school.')->group(function () {
    Route::get('/dashboard', [SchoolDashboardController::class, 'index'])->name('dashboard');

    Route::get('/booth', [BoothController::class, 'edit'])->name('booth.edit');
    Route::put('/booth', [BoothController::class, 'update'])->name('booth.update');
    Route::post('/booth/publish', [BoothController::class, 'publish'])->name('booth.publish');
    Route::post('/booth/unpublish', [BoothController::class, 'unpublish'])->name('booth.unpublish');

    Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
    Route::post('/programs', [ProgramController::class, 'store'])->name('programs.store');
    Route::put('/programs/{program}', [ProgramController::class, 'update'])->name('programs.update');
    Route::delete('/programs/{program}', [ProgramController::class, 'destroy'])->name('programs.destroy');

    Route::get('/gallery', [GalleryImageController::class, 'index'])->name('gallery.index');
    Route::post('/gallery', [GalleryImageController::class, 'store'])->name('gallery.store');
    Route::delete('/gallery/{image}', [GalleryImageController::class, 'destroy'])->name('gallery.destroy');

    Route::get('/applications', [SchoolApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}/cv', [SchoolApplicationController::class, 'downloadCv'])->name('applications.cv');
});

// Admin login (guest only)
Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
});

// Admin area
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/schools', [AdminSchoolController::class, 'index'])->name('schools.index');
    Route::get('/schools/create', [AdminSchoolController::class, 'create'])->name('schools.create');
    Route::post('/schools', [AdminSchoolController::class, 'store'])->name('schools.store');
    Route::get('/schools/{school}', [AdminSchoolController::class, 'show'])->name('schools.show');
    Route::get('/schools/{school}/edit', [AdminSchoolController::class, 'edit'])->name('schools.edit');
    Route::put('/schools/{school}', [AdminSchoolController::class, 'update'])->name('schools.update');
    Route::put('/schools/{school}/password', [AdminSchoolController::class, 'updatePassword'])->name('schools.update-password');
    Route::post('/schools/{school}/publish', [AdminSchoolController::class, 'publish'])->name('schools.publish');
    Route::post('/schools/{school}/unpublish', [AdminSchoolController::class, 'unpublish'])->name('schools.unpublish');
    Route::post('/schools/{school}/suspend', [AdminSchoolController::class, 'suspend'])->name('schools.suspend');
    Route::post('/schools/{school}/reactivate', [AdminSchoolController::class, 'reactivate'])->name('schools.reactivate');
    Route::delete('/schools/{school}', [AdminSchoolController::class, 'destroy'])->name('schools.destroy');

    Route::get('/schools/{school}/programs', [AdminProgramController::class, 'index'])->name('schools.programs.index');
    Route::post('/schools/{school}/programs', [AdminProgramController::class, 'store'])->name('schools.programs.store');
    Route::put('/schools/{school}/programs/{program}', [AdminProgramController::class, 'update'])->name('schools.programs.update');
    Route::delete('/schools/{school}/programs/{program}', [AdminProgramController::class, 'destroy'])->name('schools.programs.destroy');

    Route::get('/schools/{school}/gallery', [AdminGalleryImageController::class, 'index'])->name('schools.gallery.index');
    Route::post('/schools/{school}/gallery', [AdminGalleryImageController::class, 'store'])->name('schools.gallery.store');
    Route::delete('/schools/{school}/gallery/{image}', [AdminGalleryImageController::class, 'destroy'])->name('schools.gallery.destroy');

    Route::get('/teachers', [AdminTeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/{teacher}/edit', [AdminTeacherController::class, 'edit'])->name('teachers.edit');
    Route::put('/teachers/{teacher}/password', [AdminTeacherController::class, 'updatePassword'])->name('teachers.update-password');
    Route::post('/teachers/{teacher}/suspend', [AdminTeacherController::class, 'suspend'])->name('teachers.suspend');
    Route::post('/teachers/{teacher}/reactivate', [AdminTeacherController::class, 'reactivate'])->name('teachers.reactivate');
    Route::delete('/teachers/{teacher}', [AdminTeacherController::class, 'destroy'])->name('teachers.destroy');

    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}/cv', [AdminApplicationController::class, 'downloadCv'])->name('applications.cv');

    Route::get('/homepage-content', [HomepageContentController::class, 'edit'])->name('homepage.edit');
    Route::put('/homepage-content', [HomepageContentController::class, 'update'])->name('homepage.update');

    Route::get('/booth-settings', [BoothSettingsController::class, 'edit'])->name('booth-settings.edit');
    Route::put('/booth-settings', [BoothSettingsController::class, 'update'])->name('booth-settings.update');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');

    Route::post('/password-reset-requests/{passwordResetRequest}/approve', [PasswordResetRequestController::class, 'approve'])->name('password-reset-requests.approve');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

require __DIR__.'/auth.php';
