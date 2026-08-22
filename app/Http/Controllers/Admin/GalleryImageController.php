<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\School;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GalleryImageController extends Controller
{
    public function index(School $school): View
    {
        return view('admin.schools.gallery', [
            'school' => $school,
            'images' => $school->galleryImages,
        ]);
    }

    public function store(Request $request, School $school): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $path = $request->file('image')->store('gallery', 'public');

        $school->galleryImages()->create([
            'path' => $path,
            'sort_order' => $school->galleryImages()->count(),
        ]);

        ActivityLogger::log('admin.gallery_image_uploaded', "Admin uploaded a gallery image for {$school->name}", $school);

        return back()->with('status', 'Image uploaded.');
    }

    public function destroy(School $school, GalleryImage $image): RedirectResponse
    {
        abort_if($image->school_id !== $school->id, 404);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        ActivityLogger::log('admin.gallery_image_removed', "Admin removed a gallery image for {$school->name}", $school);

        return back()->with('status', 'Image removed.');
    }
}
