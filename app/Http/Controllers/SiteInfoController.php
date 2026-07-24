<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSiteInfoRequest;
use App\Models\DailyEntry;
use App\Services\DailyEntryService;
use Illuminate\Http\RedirectResponse;

class SiteInfoController extends Controller
{
    public function __construct(
        protected DailyEntryService $dailyEntryService,
    ) {}

    public function update(UpdateSiteInfoRequest $request, DailyEntry $dailyEntry): RedirectResponse
    {
        $this->dailyEntryService->upsertSiteInfo($dailyEntry, $request->validated());

        return redirect()->back()->with('success', 'Info site berhasil disimpan.');
    }
}
