<?php

namespace App\Http\Controllers;

use App\Enums\PitOwner;
use App\Http\Requests\StorePitRequest;
use App\Http\Requests\UpdatePitRequest;
use App\Models\Pit;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PitController extends Controller
{
    public function index(Request $request): Response
    {
        $activeSiteId = $request->session()->get('active_site_id');

        $pits = Pit::query()
            ->with('site:id,code,name')
            ->when($activeSiteId, fn ($q) => $q->where('site_id', $activeSiteId))
            ->orderBy('code')
            ->get()
            ->map(fn (Pit $pit) => [
                'id' => $pit->id,
                'site_id' => $pit->site_id,
                'code' => $pit->code,
                'owner' => $pit->owner->value,
                'owner_label' => $pit->owner->label(),
                'is_active' => $pit->is_active,
                'site' => $pit->site,
            ]);

        return Inertia::render('pits/Index', [
            'pits' => $pits,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('pits/Create', [
            'sites' => Site::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'ownerOptions' => PitOwner::options(),
        ]);
    }

    public function store(StorePitRequest $request): RedirectResponse
    {
        Pit::create($request->validated());

        return redirect()->route('pits.index')->with('success', 'PIT berhasil ditambahkan.');
    }

    public function edit(Pit $pit): Response
    {
        return Inertia::render('pits/Edit', [
            'pit' => [
                'id' => $pit->id,
                'site_id' => $pit->site_id,
                'code' => $pit->code,
                'owner' => $pit->owner->value,
                'is_active' => $pit->is_active,
            ],
            'sites' => Site::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'ownerOptions' => PitOwner::options(),
        ]);
    }

    public function update(UpdatePitRequest $request, Pit $pit): RedirectResponse
    {
        $pit->update($request->validated());

        return redirect()->route('pits.index')->with('success', 'PIT berhasil diperbarui.');
    }

    public function destroy(Pit $pit): RedirectResponse
    {
        $pit->delete();

        return redirect()->route('pits.index')->with('success', 'PIT berhasil dihapus.');
    }
}
