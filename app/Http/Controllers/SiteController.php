<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('sites/Index', [
            'sites' => Site::query()->orderBy('code')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('sites/Create');
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        Site::create($request->validated());

        return redirect()->route('sites.index')->with('success', 'Site berhasil ditambahkan.');
    }

    public function edit(Site $site): Response
    {
        return Inertia::render('sites/Edit', [
            'site' => $site,
        ]);
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $site->update($request->validated());

        return redirect()->route('sites.index')->with('success', 'Site berhasil diperbarui.');
    }

    public function destroy(Site $site): RedirectResponse
    {
        $site->delete();

        return redirect()->route('sites.index')->with('success', 'Site berhasil dihapus.');
    }
}
