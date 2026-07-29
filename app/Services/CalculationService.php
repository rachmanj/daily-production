<?php

namespace App\Services;

use App\Enums\EntryStatus;
use App\Enums\MaterialType;
use App\Enums\PlanMetric;
use App\Models\FuelRecord;
use App\Models\HourlyProductionRecord;
use App\Models\MaterialDailyPlan;
use App\Models\MonthlyPlan;
use App\Models\PlanTarget;
use App\Models\ProductionRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CalculationService
{
    public function __construct(
        protected EquipmentApiService $equipmentApiService,
    ) {}

    public function mtd(int $siteId, Carbon $date, string $metric): float
    {
        $cacheKey = "calc:mtd:{$siteId}:{$date->format('Y-m')}:{$metric}";

        return (float) Cache::remember($cacheKey, 3600, function () use ($siteId, $date, $metric) {
            return $this->sumMetricForPeriod(
                $siteId,
                $date->copy()->startOfMonth(),
                $date->copy()->endOfDay(),
                $metric,
            );
        });
    }

    public function ytd(int $siteId, Carbon $date, string $metric): float
    {
        $cacheKey = "calc:ytd:{$siteId}:{$date->format('Y')}:{$metric}";

        return (float) Cache::remember($cacheKey, 3600, function () use ($siteId, $date, $metric) {
            return $this->sumMetricForPeriod(
                $siteId,
                $date->copy()->startOfYear(),
                $date->copy()->endOfDay(),
                $metric,
            );
        });
    }

    public function ptd(int $siteId, Carbon $date, string $metric): float
    {
        return $this->mtd($siteId, $date, $metric);
    }

    public function strippingRatio(float $obBcm, float $coalTon): ?float
    {
        if ($coalTon <= 0) {
            return null;
        }

        return round($obBcm / $coalTon, 4);
    }

    public function siteStrippingRatio(int $siteId, Carbon $date): ?float
    {
        $ob = $this->mtd($siteId, $date, 'ob_removal_bcm');
        $coal = $this->mtd($siteId, $date, 'coal_getting_ton');

        return $this->strippingRatio($ob, $coal);
    }

    public function fcr(int $equipmentId, Carbon $from, Carbon $to): ?float
    {
        $cacheKey = "calc:fcr:{$equipmentId}:{$from->toDateString()}:{$to->toDateString()}";

        return Cache::remember($cacheKey, 3600, function () use ($equipmentId, $from, $to) {
            $totals = FuelRecord::query()
                ->where('equipment_id', $equipmentId)
                ->whereHas('dailyEntry', function ($q) use ($from, $to) {
                    $q->where('status', EntryStatus::Approved)
                        ->whereBetween('production_date', [$from->toDateString(), $to->toDateString()]);
                })
                ->selectRaw('COALESCE(SUM(liters), 0) as total_liters, COALESCE(SUM(working_hours), 0) as total_hours')
                ->first();

            $hours = (float) ($totals->total_hours ?? 0);

            if ($hours <= 0) {
                return null;
            }

            return round((float) ($totals->total_liters ?? 0) / $hours, 4);
        });
    }

    public function achievement(float $actual, float $target): ?float
    {
        if ($target <= 0) {
            return null;
        }

        return round(($actual / $target) * 100, 2);
    }

    public function achievementForSite(int $siteId, Carbon $date, string $metric): ?float
    {
        $target = $this->planTarget($siteId, $date, $metric);
        if ($target === null || $target <= 0) {
            return null;
        }

        $actual = $this->mtd($siteId, $date, $metric);

        return $this->achievement($actual, $target);
    }

    public function planTarget(int $siteId, Carbon $date, string $metric): ?float
    {
        $planMetric = match ($metric) {
            'ob_removal_bcm' => PlanMetric::OB,
            'coal_getting_ton' => PlanMetric::Coal,
            'stripping_ratio' => PlanMetric::StrippingRatio,
            default => null,
        };

        if ($planMetric === null) {
            return null;
        }

        $plan = MonthlyPlan::query()
            ->where('site_id', $siteId)
            ->where('year', $date->year)
            ->where('month', $date->month)
            ->first();

        if (! $plan) {
            return null;
        }

        return (float) PlanTarget::query()
            ->where('monthly_plan_id', $plan->id)
            ->where('metric', $planMetric)
            ->sum('target_value');
    }

    public function dailyValue(int $siteId, Carbon $date, string $metric): float
    {
        return $this->sumMetricForPeriod($siteId, $date->copy()->startOfDay(), $date->copy()->endOfDay(), $metric);
    }

    public function totalForPeriod(int $siteId, Carbon $from, Carbon $to, string $metric): float
    {
        return $this->sumMetricForPeriod($siteId, $from, $to, $metric);
    }

    /**
     * @return array<int, array{date: string, ob: float, coal: float, sr: float|null}>
     */
    public function trend(int $siteId, Carbon $endDate, int $days = 30): array
    {
        $cacheKey = "calc:trend:{$siteId}:{$endDate->toDateString()}:{$days}";

        return Cache::remember($cacheKey, 1800, function () use ($siteId, $endDate, $days) {
            $start = $endDate->copy()->subDays($days - 1)->startOfDay();
            $data = [];

            for ($d = $start->copy(); $d->lte($endDate); $d->addDay()) {
                $ob = $this->dailyValue($siteId, $d, 'ob_removal_bcm');
                $coal = $this->dailyValue($siteId, $d, 'coal_getting_ton');
                $data[] = [
                    'date' => $d->toDateString(),
                    'ob' => $ob,
                    'coal' => $coal,
                    'sr' => $this->strippingRatio($ob, $coal),
                ];
            }

            return $data;
        });
    }

    /**
     * @return array<int, array{pit_id: int, pit_code: string, ob: float, coal: float}>
     */
    public function perPit(int $siteId, Carbon $date): array
    {
        $cacheKey = "calc:perpit:{$siteId}:{$date->toDateString()}";

        return Cache::remember($cacheKey, 1800, function () use ($siteId, $date) {
            return ProductionRecord::query()
                ->select([
                    'pits.id as pit_id',
                    'pits.code as pit_code',
                    DB::raw('COALESCE(SUM(production_records.ob_removal_bcm), 0) as ob'),
                    DB::raw('COALESCE(SUM(production_records.coal_getting_ton), 0) as coal'),
                ])
                ->join('daily_entries', 'daily_entries.id', '=', 'production_records.daily_entry_id')
                ->join('pits', 'pits.id', '=', 'production_records.pit_id')
                ->where('daily_entries.site_id', $siteId)
                ->where('daily_entries.status', EntryStatus::Approved)
                ->whereDate('daily_entries.production_date', $date)
                ->groupBy('pits.id', 'pits.code')
                ->get()
                ->map(fn ($row) => [
                    'pit_id' => (int) $row->pit_id,
                    'pit_code' => $row->pit_code,
                    'ob' => (float) $row->ob,
                    'coal' => (float) $row->coal,
                ])
                ->all();
        });
    }

    public function totalFuelLiters(int $siteId, Carbon $date): float
    {
        return $this->dailyValue($siteId, $date, 'fuel_liters');
    }

    public function materialDtd(int $siteId, Carbon $date, MaterialType $material): float
    {
        $key = "calc:material:dtd:{$siteId}:{$date->format('Y-m-d')}:{$material->value}";

        return (float) Cache::remember($key, 3600, function () use ($siteId, $date, $material) {
            return (float) HourlyProductionRecord::query()
                ->where('material_type', $material->value)
                ->whereHas('dailyEntry', fn ($q) => $q
                    ->where('site_id', $siteId)
                    ->whereDate('production_date', $date)
                    ->where('status', EntryStatus::Approved))
                ->sum('tonnage');
        });
    }

    public function materialMtd(int $siteId, Carbon $date, MaterialType $material): float
    {
        $key = "calc:material:mtd:{$siteId}:{$date->format('Y-m')}:{$material->value}";

        return (float) Cache::remember($key, 3600, function () use ($siteId, $date, $material) {
            return (float) HourlyProductionRecord::query()
                ->where('material_type', $material->value)
                ->whereHas('dailyEntry', fn ($q) => $q
                    ->where('site_id', $siteId)
                    ->where('status', EntryStatus::Approved)
                    ->whereBetween('production_date', [
                        $date->copy()->startOfMonth()->toDateString(),
                        $date->copy()->endOfMonth()->toDateString(),
                    ]))
                ->sum('tonnage');
        });
    }

    public function hourlyTarget(int $siteId, Carbon $date, MaterialType $material): ?float
    {
        $key = "calc:material:hourly_target:{$siteId}:{$material->value}:{$date->format('Y-m')}";

        return Cache::remember($key, 3600, function () use ($siteId, $date, $material) {
            $plan = MaterialDailyPlan::query()
                ->where('site_id', $siteId)
                ->where('material_type', $material->value)
                ->where('year', $date->year)
                ->where('month', $date->month)
                ->first();

            if (! $plan || (float) $plan->operating_hours_per_day <= 0) {
                return null;
            }

            return round((float) $plan->daily_plan_tonnage / (float) $plan->operating_hours_per_day, 2);
        });
    }

    public function materialPlanDaily(int $siteId, Carbon $date, MaterialType $material): ?float
    {
        $plan = MaterialDailyPlan::query()
            ->where('site_id', $siteId)
            ->where('material_type', $material->value)
            ->where('year', $date->year)
            ->where('month', $date->month)
            ->first();

        return $plan ? (float) $plan->daily_plan_tonnage : null;
    }

    public function materialPlanMonthly(int $siteId, Carbon $date, MaterialType $material): ?float
    {
        $plan = MaterialDailyPlan::query()
            ->where('site_id', $siteId)
            ->where('material_type', $material->value)
            ->where('year', $date->year)
            ->where('month', $date->month)
            ->first();

        return $plan ? (float) $plan->monthly_plan_tonnage : null;
    }

    /**
     * @return array{hour_slot: int, tonnage: float}|null
     */
    public function currentHourProduction(int $siteId, Carbon $date, MaterialType $material): ?array
    {
        $record = HourlyProductionRecord::query()
            ->where('material_type', $material->value)
            ->whereHas('dailyEntry', fn ($q) => $q
                ->where('site_id', $siteId)
                ->whereDate('production_date', $date)
                ->where('status', EntryStatus::Approved))
            ->orderByDesc('hour_slot')
            ->first();

        if (! $record) {
            return null;
        }

        return [
            'hour_slot' => (int) $record->hour_slot,
            'tonnage' => (float) $record->tonnage,
        ];
    }

    /**
     * @return array<int, array{hour_slot: int, total: float}>
     */
    public function hourlyShiftTotals(int $siteId, Carbon $date, MaterialType $material, ?int $shiftId = null): array
    {
        $query = HourlyProductionRecord::query()
            ->selectRaw('hour_slot, COALESCE(SUM(tonnage), 0) as total')
            ->where('material_type', $material->value)
            ->whereHas('dailyEntry', fn ($q) => $q
                ->where('site_id', $siteId)
                ->whereDate('production_date', $date)
                ->where('status', EntryStatus::Approved))
            ->groupBy('hour_slot')
            ->orderBy('hour_slot');

        if ($shiftId !== null) {
            $query->where('shift_id', $shiftId);
        }

        return $query->get()
            ->map(fn ($row) => [
                'hour_slot' => (int) $row->hour_slot,
                'total' => (float) $row->total,
            ])
            ->all();
    }

    public function invalidateSiteCache(int $siteId, Carbon $date): void
    {
        $patterns = [
            "calc:mtd:{$siteId}:",
            "calc:ytd:{$siteId}:",
            "calc:trend:{$siteId}:",
            "calc:perpit:{$siteId}:",
        ];

        // Flush known keys for the month/year
        $month = $date->format('Y-m');
        $year = $date->format('Y');
        foreach (['ob_removal_bcm', 'coal_getting_ton', 'coal_hauling_ton', 'fuel_liters'] as $metric) {
            Cache::forget("calc:mtd:{$siteId}:{$month}:{$metric}");
            Cache::forget("calc:ytd:{$siteId}:{$year}:{$metric}");
        }
        Cache::forget("calc:trend:{$siteId}:{$date->toDateString()}:30");
        Cache::forget("calc:perpit:{$siteId}:{$date->toDateString()}");

        foreach (MaterialType::cases() as $material) {
            Cache::forget("calc:material:dtd:{$siteId}:{$date->format('Y-m-d')}:{$material->value}");
            Cache::forget("calc:material:mtd:{$siteId}:{$date->format('Y-m')}:{$material->value}");
            Cache::forget("calc:material:hourly_target:{$siteId}:{$material->value}:{$date->format('Y-m')}");
        }
    }

    protected function sumMetricForPeriod(int $siteId, Carbon $from, Carbon $to, string $metric): float
    {
        if ($metric === 'fuel_liters') {
            return (float) FuelRecord::query()
                ->whereHas('dailyEntry', function ($q) use ($siteId, $from, $to) {
                    $q->where('site_id', $siteId)
                        ->where('status', EntryStatus::Approved)
                        ->whereBetween('production_date', [$from->toDateString(), $to->toDateString()]);
                })
                ->sum('liters');
        }

        $column = match ($metric) {
            'ob_removal_bcm' => 'ob_removal_bcm',
            'coal_getting_ton' => 'coal_getting_ton',
            'coal_hauling_ton' => 'coal_hauling_ton',
            default => null,
        };

        if ($column === null) {
            return 0.0;
        }

        return (float) ProductionRecord::query()
            ->whereHas('dailyEntry', function ($q) use ($siteId, $from, $to) {
                $q->where('site_id', $siteId)
                    ->where('status', EntryStatus::Approved)
                    ->whereBetween('production_date', [$from->toDateString(), $to->toDateString()]);
            })
            ->sum($column);
    }

    /** @deprecated Use mtd() */
    public function calculateMtd(int $siteId, string $metric, ?int $year = null, ?int $month = null): float
    {
        $date = Carbon::create($year ?? now()->year, $month ?? now()->month, now()->day);

        return $this->mtd($siteId, $date, $metric);
    }

    /** @deprecated Use ytd() */
    public function calculateYtd(int $siteId, string $metric, ?int $year = null): float
    {
        $date = Carbon::create($year ?? now()->year, now()->month, now()->day);

        return $this->ytd($siteId, $date, $metric);
    }

    /** @deprecated Use siteStrippingRatio() */
    public function calculateStrippingRatio(int $siteId, ?int $year = null, ?int $month = null): ?float
    {
        $date = Carbon::create($year ?? now()->year, $month ?? now()->month, now()->day);

        return $this->siteStrippingRatio($siteId, $date);
    }

    /** @deprecated */
    public function calculateFcr(int $siteId, ?int $year = null, ?int $month = null): ?float
    {
        $date = Carbon::create($year ?? now()->year, $month ?? now()->month, now()->day);

        return $this->fcr(0, $date->copy()->startOfMonth(), $date);
    }

    /** @deprecated Use achievementForSite() */
    public function calculateAchievement(int $siteId, string $metric, ?int $year = null, ?int $month = null): ?float
    {
        $date = Carbon::create($year ?? now()->year, $month ?? now()->month, now()->day);

        return $this->achievementForSite($siteId, $date, $metric);
    }
}
