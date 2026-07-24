<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEquipmentDeploymentsRequest;
use App\Models\DailyEntry;
use App\Services\DailyEntryService;
use Illuminate\Http\RedirectResponse;

class EquipmentDeploymentController extends Controller
{
    public function __construct(
        protected DailyEntryService $dailyEntryService,
    ) {}

    public function update(UpdateEquipmentDeploymentsRequest $request, DailyEntry $dailyEntry): RedirectResponse
    {
        $this->dailyEntryService->upsertEquipmentDeployments($dailyEntry, $request->validated('records'));

        return redirect()->back()->with('success', 'Data deployment berhasil disimpan.');
    }
}
