<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFuelRecordsRequest;
use App\Models\DailyEntry;
use App\Services\DailyEntryService;
use Illuminate\Http\RedirectResponse;

class FuelRecordController extends Controller
{
    public function __construct(
        protected DailyEntryService $dailyEntryService,
    ) {}

    public function update(UpdateFuelRecordsRequest $request, DailyEntry $dailyEntry): RedirectResponse
    {
        $this->dailyEntryService->upsertFuelRecords($dailyEntry, $request->validated('records'));

        return redirect()->back()->with('success', 'Data fuel berhasil disimpan.');
    }
}
