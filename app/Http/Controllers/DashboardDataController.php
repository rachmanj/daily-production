<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardDataController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    public function kpi(Request $request): JsonResponse
    {
        $siteId = $request->integer('site_id');
        $date = Carbon::parse($request->string('date', now()->toDateString()));

        return response()->json($this->dashboardService->kpi($siteId, $date));
    }

    public function trend(Request $request): JsonResponse
    {
        $siteId = $request->integer('site_id');
        $date = Carbon::parse($request->string('date', now()->toDateString()));
        $days = $request->integer('days', 30);

        return response()->json($this->dashboardService->trend($siteId, $date, $days));
    }

    public function utilization(Request $request): JsonResponse
    {
        return response()->json($this->dashboardService->utilization($request->integer('site_id')));
    }

    public function perPit(Request $request): JsonResponse
    {
        $siteId = $request->integer('site_id');
        $date = Carbon::parse($request->string('date', now()->toDateString()));

        return response()->json($this->dashboardService->perPit($siteId, $date));
    }

    public function drilldown(Request $request): JsonResponse
    {
        $siteId = $request->integer('site_id');
        $date = Carbon::parse($request->string('date', now()->toDateString()));
        $level = $request->string('level', 'pit');

        return response()->json(
            $this->dashboardService->drilldown($siteId, $date, $level, $request->integer('pit_id') ?: null)
        );
    }

    public function fuelByEquipment(Request $request): JsonResponse
    {
        $siteId = $request->integer('site_id');
        $date = Carbon::parse($request->string('date', now()->toDateString()));

        return response()->json($this->dashboardService->fuelByEquipment($siteId, $date));
    }

    public function consolidated(Request $request): JsonResponse
    {
        $siteIds = $request->collect('site_ids')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $from = Carbon::parse($request->string('date_from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->string('date_to', now()->toDateString()))->endOfDay();

        return response()->json($this->dashboardService->consolidated($siteIds, $from, $to));
    }
}
