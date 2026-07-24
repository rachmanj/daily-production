<?php

namespace App\Services;

use App\Models\FuelRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnomalyDetectionService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function detectFcrOutliers(int $siteId, Carbon $date): array
    {
        $from = $date->copy()->subDays(30);
        $records = FuelRecord::query()
            ->select([
                'equipment_id',
                'unit_code',
                DB::raw('SUM(liters) as total_liters'),
                DB::raw('SUM(working_hours) as total_hours'),
            ])
            ->whereHas('dailyEntry', function ($q) use ($siteId, $from, $date) {
                $q->where('site_id', $siteId)
                    ->whereBetween('production_date', [$from, $date]);
            })
            ->groupBy('equipment_id', 'unit_code')
            ->having('total_hours', '>', 0)
            ->get()
            ->map(fn ($r) => [
                'equipment_id' => $r->equipment_id,
                'unit_code' => $r->unit_code,
                'fcr' => (float) $r->total_liters / (float) $r->total_hours,
            ]);

        if ($records->count() < 3) {
            return [];
        }

        $fcrValues = $records->pluck('fcr');
        $mean = $fcrValues->avg();
        $stdDev = $this->standardDeviation($fcrValues->all());

        if ($stdDev <= 0) {
            return [];
        }

        return $records
            ->filter(fn ($r) => abs($r['fcr'] - $mean) > 2 * $stdDev)
            ->map(fn ($r) => array_merge($r, [
                'mean' => round($mean, 4),
                'std_dev' => round($stdDev, 4),
                'z_score' => round(($r['fcr'] - $mean) / $stdDev, 2),
            ]))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, float>  $values
     */
    protected function standardDeviation(array $values): float
    {
        $count = count($values);
        if ($count < 2) {
            return 0.0;
        }

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn ($v) => ($v - $mean) ** 2, $values)) / ($count - 1);

        return sqrt($variance);
    }
}
