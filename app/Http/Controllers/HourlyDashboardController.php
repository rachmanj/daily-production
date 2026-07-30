<?php

namespace App\Http\Controllers;

use App\Enums\EntryStatus;
use App\Enums\MaterialType;
use App\Exports\HourlyPdfExport;
use App\Exports\HourlyProductionExport;
use App\Models\HourlyProductionRecord;
use App\Models\Site;
use App\Services\CalculationService;
use App\Services\HourlyProductionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HourlyDashboardController extends Controller
{
    public function __construct(
        protected CalculationService $calculationService,
        protected HourlyProductionService $hourlyProductionService,
    ) {}

    public function index(Request $request): Response
    {
        $sites = Site::query()->whereIn('code', config('mineops.ccr_site_codes'))->orderBy('code')->get(['id', 'code', 'name']);
        $siteId = $request->integer('site_id', $sites->first()?->id ?? 0);
        $date = $request->string('date', now()->toDateString());
        $material = $request->string('material', MaterialType::Limestone->value);

        return Inertia::render('hourly/Dashboard', [
            'sites' => $sites,
            'filters' => [
                'site_id' => $siteId,
                'date' => $date,
                'material' => $material,
            ],
            'materials' => MaterialType::options(),
        ]);
    }

    public function kpi(Request $request): JsonResponse
    {
        $validated = $this->validateDashboardRequest($request);
        $siteId = (int) $validated['site_id'];
        $date = Carbon::parse($validated['date']);
        $material = MaterialType::from($validated['material']);

        $dtdActual = $this->calculationService->materialDtd($siteId, $date, $material);
        $mtdActual = $this->calculationService->materialMtd($siteId, $date, $material);
        $dtdPlan = $this->calculationService->materialPlanDaily($siteId, $date, $material);
        $mtdPlan = $this->calculationService->materialPlanMonthly($siteId, $date, $material);
        $hourlyTarget = $this->calculationService->hourlyTarget($siteId, $date, $material);
        $currentHour = $this->calculationService->currentHourProduction($siteId, $date, $material);

        return response()->json([
            'dtd' => [
                'actual' => $dtdActual,
                'plan' => $dtdPlan,
                'achievement' => $dtdPlan ? $this->calculationService->achievement($dtdActual, $dtdPlan) : null,
            ],
            'mtd' => [
                'actual' => $mtdActual,
                'plan' => $mtdPlan,
                'achievement' => $mtdPlan ? $this->calculationService->achievement($mtdActual, $mtdPlan) : null,
            ],
            'current_hour' => $currentHour ? [
                'hour_slot' => $currentHour['hour_slot'],
                'tonnage' => $currentHour['tonnage'],
                'target' => $hourlyTarget,
                'achievement' => $hourlyTarget
                    ? $this->calculationService->achievement($currentHour['tonnage'], $hourlyTarget)
                    : null,
            ] : null,
            'hourly_target' => $hourlyTarget,
        ]);
    }

    public function heatmap(Request $request): JsonResponse
    {
        $validated = $this->validateDashboardRequest($request);
        $siteId = (int) $validated['site_id'];
        $date = Carbon::parse($validated['date']);
        $material = MaterialType::from($validated['material']);
        $shiftId = $validated['shift_id'] ?? null;

        $equipment = $this->hourlyProductionService->getEquipmentGrid($siteId, $material);
        $hourlyTarget = $this->calculationService->hourlyTarget($siteId, $date, $material);

        $query = HourlyProductionRecord::query()
            ->where('material_type', $material->value)
            ->whereHas('dailyEntry', fn ($q) => $q
                ->where('site_id', $siteId)
                ->whereDate('production_date', $date)
                ->where('status', EntryStatus::Approved));

        if ($shiftId) {
            $query->where('shift_id', $shiftId);
        }

        $records = $query->get(['equipment_id', 'hour_slot', 'tonnage', 'unit_code']);

        $grid = [];
        foreach ($records as $record) {
            $grid[$record->hour_slot][$record->equipment_id] = (float) $record->tonnage;
        }

        return response()->json([
            'equipment' => $equipment,
            'hourly_target' => $hourlyTarget,
            'grid' => $grid,
        ]);
    }

    public function fleet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'site_id' => ['required', 'exists:sites,id'],
            'material' => ['required', 'string'],
        ]);

        $material = MaterialType::from($validated['material']);

        return response()->json([
            'fleet' => $this->hourlyProductionService->getFleetStatus(
                (int) $validated['site_id'],
                $material,
            ),
        ]);
    }

    public function trend(Request $request): JsonResponse
    {
        $validated = $this->validateDashboardRequest($request);
        $siteId = (int) $validated['site_id'];
        $date = Carbon::parse($validated['date']);
        $material = MaterialType::from($validated['material']);
        $shiftId = isset($validated['shift_id']) ? (int) $validated['shift_id'] : null;

        return response()->json([
            'trend' => $this->calculationService->hourlyShiftTotals($siteId, $date, $material, $shiftId),
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $validated = $this->validateDashboardRequest($request);
        $format = $request->string('format', 'excel');

        if ($format === 'pdf') {
            $export = new HourlyPdfExport(
                (int) $validated['site_id'],
                Carbon::parse($validated['date']),
                MaterialType::from($validated['material']),
                $this->calculationService,
                $this->hourlyProductionService,
            );

            return $export->download();
        }

        $export = new HourlyProductionExport(
            (int) $validated['site_id'],
            Carbon::parse($validated['date']),
            MaterialType::from($validated['material']),
            $this->hourlyProductionService,
        );

        return $export->download();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateDashboardRequest(Request $request): array
    {
        return $request->validate([
            'site_id' => ['required', 'exists:sites,id'],
            'date' => ['required', 'date'],
            'material' => ['required', 'string'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
        ]);
    }
}
