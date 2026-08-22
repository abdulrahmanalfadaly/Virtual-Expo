<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GalleryImageController extends Controller
{
    public function index(Request $request): View
    {
        $school = $request->user()->school;

        return view('school.gallery.index', [
            'school' => $school,
            'images' => $school->galleryImages,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $school = $request->user()->school;

        $path = $request->file('image')->store('gallery', 'public');

        $school->galleryImages()->create([
            'path' => $path,
            'sort_order' => $school->galleryImages()->count(),
        ]);

        ActivityLogger::log('gallery.image_uploaded', "{$school->name} uploaded a gallery image", $school);

        return back()->with('status', 'Image uploaded.');
    }

    public function destroy(Request $request, GalleryImage $image): RedirectResponse
    {
        $this->authorize('delete', $image);

        Storage::disk('public')->delete($image->path);
        $school = $image->school;
        $image->delete();

        ActivityLogger::log('gallery.image_removed', "{$school->name} removed a gallery image", $school);

        return back()->with('status', 'Image removed.');
    }
}
