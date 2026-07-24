<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActiveSiteController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_id' => ['required', 'exists:sites,id'],
        ]);

        $request->session()->put('active_site_id', $validated['site_id']);

        return back();
    }
}
