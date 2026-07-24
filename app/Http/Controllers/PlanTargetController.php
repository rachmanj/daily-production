<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePlanTargetsRequest;
use App\Models\MonthlyPlan;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;

class PlanTargetController extends Controller
{
    public function __construct(
        protected PlanService $planService,
    ) {}

    public function update(UpdatePlanTargetsRequest $request, MonthlyPlan $monthlyPlan): RedirectResponse
    {
        $this->planService->upsertTargets($monthlyPlan, $request->validated('targets'));

        return redirect()->back()->with('success', 'Target plan berhasil disimpan.');
    }
}
