<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteSwitchController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_id' => ['required', 'exists:sites,id'],
        ]);

        $request->session()->put('active_site_id', $validated['site_id']);

        return back()->with('success', 'Site aktif berhasil diubah.');
    }
}
