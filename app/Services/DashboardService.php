<?php

namespace App\Services;

use App\Models\EquipmentAssignment;
use App\Models\FuelRecord;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function __construct(
        protected CalculationService $calculationService,
        protected EquipmentApiService $equipmentApiService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function kpi(int $siteId, Carbon $date): array
    {
        $cacheKey = "dashboard:kpi:{$siteId}:{$date->toDateString()}";

        return Cache::remember($cacheKey, 1800, function () use ($siteId, $date) {
            $obToday = $this->calculationService->dailyValue($siteId, $date, 'ob_removal_bcm');
            $coalToday = $this->calculationService->dailyValue($siteId, $date, 'coal_getting_ton');
            $obMtd = $this->calculationService->mtd($siteId, $date, 'ob_removal_bcm');
            $coalMtd = $this->calculationService->mtd($siteId, $date, 'coal_getting_ton');
            $fuelToday = $this->calculationService->totalFuelLiters($siteId, $date);

            return [
                'ob' => [
                    'today' => $obToday,
                    'mtd' => $obMtd,
                    'achievement' => $this->calculationService->achievementForSite($siteId, $date, 'ob_removal_bcm'),
                ],
                'coal' => [
                    'today' => $coalToday,
                    'mtd' => $coalMtd,
                    'achievement' => $this->calculationService->achievementForSite($siteId, $date, 'coal_getting_ton'),
                ],
                'stripping_ratio' => [
                    'mtd' => $this->calculationService->siteStrippingRatio($siteId, $date),
                    'ytd' => $this->calculationService->siteStrippingRatio(
                        $siteId,
                        $date->copy()->endOfYear()->min($date),
                    ),
                ],
                'fuel' => [
                    'today_liters' => $fuelToday,
                    'mtd_liters' => $this->calculationService->mtd($siteId, $date, 'fuel_liters'),
                ],
            ];
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function trend(int $siteId, Carbon $date, int $days = 30): array
    {
        return $this->calculationService->trend($siteId, $date, $days);
    }

    /**
     * @return array<string, mixed>
     */
    public function utilization(int $siteId): array
    {
        $assignments = EquipmentAssignment::query()
            ->where('site_id', $siteId)
            ->where('is_active_for_tracking', true)
            ->get();

        $active = $assignments->where('is_rfu', false)->count();
        $standby = $assignments->where('is_rfu', true)->count();

        return [
            'active' => $active,
            'standby' => $standby,
            'breakdown' => 0,
            'total' => $assignments->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function perPit(int $siteId, Carbon $date): array
    {
        return $this->calculationService->perPit($siteId, $date);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fuelByEquipment(int $siteId, Carbon $date): array
    {
        return FuelRecord::query()
            ->selectRaw('equipment_id, unit_code, SUM(liters) as liters, SUM(working_hours) as hours')
            ->whereHas('dailyEntry', function ($q) use ($siteId, $date) {
                $q->where('site_id', $siteId)
                    ->whereDate('production_date', $date);
            })
            ->groupBy('equipment_id', 'unit_code')
            ->get()
            ->map(fn ($row) => [
                'equipment_id' => $row->equipment_id,
                'unit_code' => $row->unit_code,
                'liters' => (float) $row->liters,
                'hours' => (float) $row->hours,
                'fcr' => $row->hours > 0 ? round($row->liters / $row->hours, 2) : null,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function drilldown(int $siteId, Carbon $date, string $level, ?int $pitId = null): array
    {
        if ($level === 'pit') {
            return ['items' => $this->perPit($siteId, $date)];
        }

        return ['items' => []];
    }

    /**
     * @param  array<int, int>  $siteIds
     * @return array<string, mixed>
     */
    public function consolidated(array $siteIds, Carbon $from, Carbon $to): array
    {
        if ($siteIds === []) {
            $siteIds = Site::query()
                ->where('is_active', true)
                ->orderBy('code')
                ->pluck('id')
                ->all();
        }

        $sites = Site::query()
            ->whereIn('id', $siteIds)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $perSite = [];
        $grandOb = 0.0;
        $grandCoal = 0.0;
        $grandHauling = 0.0;
        $grandFuel = 0.0;

        foreach ($sites as $site) {
            $ob = $this->calculationService->totalForPeriod($site->id, $from, $to, 'ob_removal_bcm');
            $coal = $this->calculationService->totalForPeriod($site->id, $from, $to, 'coal_getting_ton');
            $hauling = $this->calculationService->totalForPeriod($site->id, $from, $to, 'coal_hauling_ton');
            $fuel = $this->calculationService->totalForPeriod($site->id, $from, $to, 'fuel_liters');

            $perSite[] = [
                'site_id' => $site->id,
                'site_code' => $site->code,
                'site_name' => $site->name,
                'ob' => $ob,
                'coal' => $coal,
                'hauling' => $hauling,
                'fuel_liters' => $fuel,
                'sr' => $this->calculationService->strippingRatio($ob, $coal),
            ];

            $grandOb += $ob;
            $grandCoal += $coal;
            $grandHauling += $hauling;
            $grandFuel += $fuel;
        }

        $trend = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $dayOb = 0.0;
            $dayCoal = 0.0;

            foreach ($siteIds as $siteId) {
                $dayOb += $this->calculationService->dailyValue($siteId, $d, 'ob_removal_bcm');
                $dayCoal += $this->calculationService->dailyValue($siteId, $d, 'coal_getting_ton');
            }

            $trend[] = [
                'date' => $d->toDateString(),
                'ob' => $dayOb,
                'coal' => $dayCoal,
                'sr' => $this->calculationService->strippingRatio($dayOb, $dayCoal),
            ];
        }

        return [
            'totals' => [
                'ob' => $grandOb,
                'coal' => $grandCoal,
                'hauling' => $grandHauling,
                'fuel_liters' => $grandFuel,
                'sr' => $this->calculationService->strippingRatio($grandOb, $grandCoal),
            ],
            'sites' => $perSite,
            'trend' => $trend,
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
        ];
    }
}
