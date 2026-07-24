<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\PlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VarianceController extends Controller
{
    public function __construct(
        protected PlanService $planService,
    ) {}

    public function index(Request $request): Response
    {
        $siteId = $request->integer('site_id') ?: $request->session()->get('active_site_id');
        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);

        return Inertia::render('variance/Index', [
            'sites' => Site::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'filters' => compact('siteId', 'year', 'month'),
            'variance' => $this->planService->varianceAnalysis($siteId, $year, $month),
            'lossContribution' => $this->planService->lossContribution($siteId, $year, $month),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json([
            'variance' => $this->planService->varianceAnalysis(
                $request->integer('site_id'),
                $request->integer('year', now()->year),
                $request->integer('month', now()->month),
            ),
            'loss' => $this->planService->lossContribution(
                $request->integer('site_id'),
                $request->integer('year', now()->year),
                $request->integer('month', now()->month),
            ),
        ]);
    }
}
