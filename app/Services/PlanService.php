<?php

namespace App\Services;

use App\Enums\EntryStatus;
use App\Enums\PlanMetric;
use App\Models\MonthlyPlan;
use App\Models\SiteInfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlanService
{
    public function __construct(
        protected CalculationService $calculationService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createPlan(array $data, int $userId): MonthlyPlan
    {
        return MonthlyPlan::create([
            'site_id' => $data['site_id'],
            'year' => $data['year'],
            'month' => $data['month'],
            'created_by' => $userId,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $targets
     */
    public function upsertTargets(MonthlyPlan $plan, array $targets): void
    {
        DB::transaction(function () use ($plan, $targets) {
            $plan->planTargets()->delete();

            foreach ($targets as $target) {
                $plan->planTargets()->create($target);
            }
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function varianceAnalysis(int $siteId, int $year, int $month): array
    {
        $date = Carbon::create($year, $month, min(now()->day, Carbon::create($year, $month)->daysInMonth));
        $plan = MonthlyPlan::query()
            ->where('site_id', $siteId)
            ->where('year', $year)
            ->where('month', $month)
            ->with('planTargets.pit')
            ->first();

        if (! $plan) {
            return [];
        }

        $results = [];
        foreach ($plan->planTargets as $target) {
            $metricKey = match ($target->metric) {
                PlanMetric::OB => 'ob_removal_bcm',
                PlanMetric::Coal => 'coal_getting_ton',
                default => null,
            };

            if ($metricKey === null) {
                continue;
            }

            $actual = $this->actualForPit($siteId, $target->pit_id, $date, $metricKey);
            $planValue = (float) $target->target_value;
            $variance = $planValue - $actual;
            $variancePct = $planValue > 0 ? round(($variance / $planValue) * 100, 2) : null;

            $results[] = [
                'pit_id' => $target->pit_id,
                'pit_code' => $target->pit->code,
                'metric' => $target->metric->value,
                'metric_label' => $target->metric->label(),
                'owner' => $target->owner->value,
                'plan' => $planValue,
                'actual' => $actual,
                'variance' => $variance,
                'variance_pct' => $variancePct,
                'achievement' => $this->calculationService->achievement($actual, $planValue),
            ];
        }

        return $results;
    }

    /**
     * @return array<string, mixed>
     */
    public function lossContribution(int $siteId, int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();

        $siteInfos = SiteInfo::query()
            ->whereHas('dailyEntry', function ($q) use ($siteId, $start, $end) {
                $q->where('site_id', $siteId)
                    ->where('status', EntryStatus::Approved)
                    ->whereBetween('production_date', [$start, $end]);
            })
            ->get();

        $totalRain = $siteInfos->sum('rain_hours');
        $totalSlippery = $siteInfos->sum('slippery_hours');
        $rainDays = $siteInfos->where('rain_hours', '>', 0)->count();
        $slipperyDays = $siteInfos->where('slippery_hours', '>', 0)->count();

        return [
            'total_rain_hours' => (float) $totalRain,
            'total_slippery_hours' => (float) $totalSlippery,
            'rain_days' => $rainDays,
            'slippery_days' => $slipperyDays,
        ];
    }

    protected function actualForPit(int $siteId, int $pitId, Carbon $date, string $metric): float
    {
        $column = match ($metric) {
            'ob_removal_bcm' => 'ob_removal_bcm',
            'coal_getting_ton' => 'coal_getting_ton',
            default => 'ob_removal_bcm',
        };

        return (float) DB::table('production_records')
            ->join('daily_entries', 'daily_entries.id', '=', 'production_records.daily_entry_id')
            ->where('daily_entries.site_id', $siteId)
            ->where('daily_entries.status', EntryStatus::Approved)
            ->where('production_records.pit_id', $pitId)
            ->whereBetween('daily_entries.production_date', [
                $date->copy()->startOfMonth()->toDateString(),
                $date->toDateString(),
            ])
            ->sum("production_records.{$column}");
    }
}
