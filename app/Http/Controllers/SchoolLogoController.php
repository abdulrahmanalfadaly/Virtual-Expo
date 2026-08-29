<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SchoolLogoController extends Controller
{
    /**
     * Send the stored logo back as a real download.
     *
     * Logos live on the public disk under a hashed filename, so linking
     * straight at the storage URL would hand over something like
     * "9f2c1e....png". Streaming it through here lets the file arrive named
     * after the school it belongs to.
     */
    public function download(School $school): StreamedResponse
    {
        $user = auth()->user();

        abort_unless($user?->isAdmin() || $user?->school?->is($school), 403);

        abort_if(! $school->logo_path || ! Storage::disk('public')->exists($school->logo_path), 404);

        $extension = pathinfo($school->logo_path, PATHINFO_EXTENSION) ?: 'png';
        $filename = Str::slug($school->name ?: 'school').'-logo.'.$extension;

        return Storage::disk('public')->download($school->logo_path, $filename);
    }
}
