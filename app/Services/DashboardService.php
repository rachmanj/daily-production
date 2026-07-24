<?php

namespace App\Services;

use App\Models\EquipmentAssignment;
use App\Models\FuelRecord;
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
}
