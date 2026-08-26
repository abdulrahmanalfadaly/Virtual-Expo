<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GuestAccessController extends Controller
{
    public function enter(Request $request, string $token): RedirectResponse
    {
        $expected = SiteSetting::get('guest_link_token');

        if (! $expected || ! hash_equals($expected, $token)) {
            abort(404);
        }

        $request->session()->put('guest_access', true);

        return redirect()->route('home');
    }
}
