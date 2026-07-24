<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMonthlyPlanRequest;
use App\Models\MonthlyPlan;
use App\Models\Pit;
use App\Models\Site;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonthlyPlanController extends Controller
{
    public function __construct(
        protected PlanService $planService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('monthly-plans/Index', [
            'plans' => MonthlyPlan::query()
                ->with('site:id,code,name')
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->paginate(20),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('monthly-plans/Create', [
            'sites' => Site::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'pits' => Pit::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'site_id', 'owner']),
        ]);
    }

    public function store(StoreMonthlyPlanRequest $request): RedirectResponse
    {
        $plan = $this->planService->createPlan($request->validated(), $request->user()->id);

        if ($request->filled('targets')) {
            $this->planService->upsertTargets($plan, $request->validated('targets'));
        }

        return redirect()->route('monthly-plans.edit', $plan)->with('success', 'Plan bulanan berhasil dibuat.');
    }

    public function edit(MonthlyPlan $monthlyPlan): Response
    {
        $monthlyPlan->load(['planTargets.pit', 'site']);

        return Inertia::render('monthly-plans/Edit', [
            'plan' => $monthlyPlan,
            'pits' => Pit::query()->where('site_id', $monthlyPlan->site_id)->where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function update(Request $request, MonthlyPlan $monthlyPlan): RedirectResponse
    {
        $monthlyPlan->update($request->only(['year', 'month']));

        return redirect()->back()->with('success', 'Plan berhasil diperbarui.');
    }

    public function destroy(MonthlyPlan $monthlyPlan): RedirectResponse
    {
        $monthlyPlan->delete();

        return redirect()->route('monthly-plans.index')->with('success', 'Plan berhasil dihapus.');
    }
}
