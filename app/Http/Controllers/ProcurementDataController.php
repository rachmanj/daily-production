<?php

namespace App\Http\Controllers;

use App\Services\ProcurementApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProcurementDataController extends Controller
{
    public function __construct(
        protected ProcurementApiService $procurementApiService,
    ) {}

    public function poSent(Request $request): JsonResponse
    {
        $code = $this->resolveProjectCode($request);

        return response()->json($this->procurementApiService->poSent(
            $code,
            $request->integer('year', now()->year),
            $request->integer('month', now()->month),
        ));
    }

    public function grpo(Request $request): JsonResponse
    {
        $code = $this->resolveProjectCode($request);

        return response()->json($this->procurementApiService->grpo(
            $code,
            $request->integer('year', now()->year),
            $request->integer('month', now()->month),
        ));
    }

    public function npi(Request $request): JsonResponse
    {
        $code = $this->resolveProjectCode($request);

        return response()->json($this->procurementApiService->npi(
            $code,
            $request->integer('year', now()->year),
            $request->integer('month', now()->month),
        ));
    }

    public function budget(Request $request): JsonResponse
    {
        $code = $this->resolveProjectCode($request);

        return response()->json($this->procurementApiService->budget(
            $code,
            $request->integer('year', now()->year),
            $request->integer('month', now()->month),
            $request->string('type', 'regular'),
        ));
    }

    public function allProjects(Request $request): JsonResponse
    {
        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);
        $codes = $this->procurementApiService->allProjectCodes();

        return response()->json([
            'projects' => collect($codes)->map(fn ($code) => [
                'project_code' => $code,
                'grpo' => $this->procurementApiService->grpo($code, $year, $month),
                'npi' => $this->procurementApiService->npi($code, $year, $month),
            ]),
        ]);
    }

    protected function resolveProjectCode(Request $request): string
    {
        if ($request->filled('project_code')) {
            return $request->string('project_code');
        }

        $siteId = $request->integer('site_id') ?: $request->session()->get('active_site_id');

        return $this->procurementApiService->projectCodeForSite($siteId) ?? '022C';
    }
}
