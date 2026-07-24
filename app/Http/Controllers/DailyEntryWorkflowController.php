<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveDailyEntryRequest;
use App\Http\Requests\SubmitDailyEntryRequest;
use App\Models\DailyEntry;
use App\Services\CalculationService;
use App\Services\DailyEntryService;
use Illuminate\Http\RedirectResponse;

class DailyEntryWorkflowController extends Controller
{
    public function __construct(
        protected DailyEntryService $dailyEntryService,
        protected CalculationService $calculationService,
    ) {}

    public function submit(SubmitDailyEntryRequest $request, DailyEntry $dailyEntry): RedirectResponse
    {
        $this->dailyEntryService->submit($dailyEntry);

        return redirect()->route('daily-entries.show', $dailyEntry)
            ->with('success', 'Entry berhasil disubmit untuk approval.');
    }

    public function approve(ApproveDailyEntryRequest $request, DailyEntry $dailyEntry): RedirectResponse
    {
        $this->dailyEntryService->approve($dailyEntry, $request->user()->id, $this->calculationService);

        return redirect()->route('daily-entries.show', $dailyEntry)
            ->with('success', 'Entry berhasil disetujui.');
    }

    public function reject(ApproveDailyEntryRequest $request, DailyEntry $dailyEntry): RedirectResponse
    {
        $this->authorize('reject', $dailyEntry);
        $this->dailyEntryService->reject($dailyEntry);

        return redirect()->route('daily-entries.edit', $dailyEntry)
            ->with('success', 'Entry ditolak dan dikembalikan ke draf.');
    }
}
