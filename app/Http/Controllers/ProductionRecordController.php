<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProductionRecordsRequest;
use App\Models\DailyEntry;
use App\Services\DailyEntryService;
use Illuminate\Http\RedirectResponse;

class ProductionRecordController extends Controller
{
    public function __construct(
        protected DailyEntryService $dailyEntryService,
    ) {}

    public function update(UpdateProductionRecordsRequest $request, DailyEntry $dailyEntry): RedirectResponse
    {
        $this->dailyEntryService->upsertProductionRecords($dailyEntry, $request->validated('records'));

        return redirect()->back()->with('success', 'Data produksi berhasil disimpan.');
    }
}
