<?php

namespace App\Services;

use App\Enums\MaterialType;
use App\Models\DailyEntry;
use App\Models\EquipmentAssignment;
use App\Models\HourlyProductionRecord;
use App\Models\Pit;
use App\Models\ProductionRecord;
use App\Models\TripProductionRecord;
use Illuminate\Support\Facades\DB;

class TripAggregationService
{
    public function __construct(
        protected CalculationService $calculationService,
    ) {}

    public function rollupTripToHourly(int $dailyEntryId): void
    {
        $entry = DailyEntry::query()->with('site')->findOrFail($dailyEntryId);
        $siteCode = $entry->site->code;
        $coalDensity = $this->coalDensityFactor($siteCode);

        $trips = TripProductionRecord::query()
            ->where('daily_entry_id', $dailyEntryId)
            ->get();

        if ($trips->isEmpty()) {
            return;
        }

        $materials = $trips->pluck('material_type')->unique()->map(fn (MaterialType $m) => $m->value)->all();

        DB::transaction(function () use ($dailyEntryId, $trips, $coalDensity, $materials) {
            HourlyProductionRecord::query()
                ->where('daily_entry_id', $dailyEntryId)
                ->whereIn('material_type', $materials)
                ->delete();

            $aggregates = [];

            foreach ($trips as $trip) {
                if (! $trip->excavator_id) {
                    continue;
                }

                $key = "{$trip->excavator_id}|{$trip->material_type->value}|{$trip->hour_slot}|{$trip->shift_id}";
                $tonnage = $this->tripVolumeToTonnage($trip, $coalDensity);

                if (! isset($aggregates[$key])) {
                    $aggregates[$key] = [
                        'equipment_id' => $trip->excavator_id,
                        'unit_code' => $trip->excavator_code,
                        'material_type' => $trip->material_type->value,
                        'hour_slot' => $trip->hour_slot,
                        'shift_id' => $trip->shift_id,
                        'tonnage' => 0.0,
                    ];
                }

                $aggregates[$key]['tonnage'] += $tonnage;
            }

            foreach ($aggregates as $row) {
                if ($row['tonnage'] <= 0) {
                    continue;
                }

                $assignment = EquipmentAssignment::query()
                    ->where('equipment_id', $row['equipment_id'])
                    ->first();

                HourlyProductionRecord::updateOrCreate(
                    [
                        'daily_entry_id' => $dailyEntryId,
                        'equipment_id' => $row['equipment_id'],
                        'material_type' => $row['material_type'],
                        'hour_slot' => $row['hour_slot'],
                    ],
                    [
                        'unit_code' => $row['unit_code'],
                        'shift_id' => $row['shift_id'],
                        'pit_id' => $assignment?->pit_id,
                        'tonnage' => round($row['tonnage'], 2),
                    ],
                );
            }
        });
    }

    public function rollupTripToDaily(int $dailyEntryId): void
    {
        $entry = DailyEntry::query()->with('site')->findOrFail($dailyEntryId);
        $siteCode = $entry->site->code;
        $coalDensity = $this->coalDensityFactor($siteCode);

        $trips = TripProductionRecord::query()
            ->where('daily_entry_id', $dailyEntryId)
            ->get();

        if ($trips->isEmpty()) {
            return;
        }

        $defaultPitId = Pit::query()
            ->where('site_id', $entry->site_id)
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id');

        if (! $defaultPitId) {
            return;
        }

        DB::transaction(function () use ($dailyEntryId, $trips, $coalDensity, $defaultPitId) {
            $byShift = [];

            foreach ($trips as $trip) {
                $shiftId = $trip->shift_id;

                if (! isset($byShift[$shiftId])) {
                    $byShift[$shiftId] = [
                        'ob_removal_bcm' => 0.0,
                        'coal_getting_ton' => 0.0,
                        'haulers' => [],
                    ];
                }

                $volume = (float) $trip->volume_bcm;

                match ($trip->material_type) {
                    MaterialType::Overburden => $byShift[$shiftId]['ob_removal_bcm'] += $volume,
                    MaterialType::Coal => $byShift[$shiftId]['coal_getting_ton'] += $volume * $coalDensity,
                    default => null,
                };

                if ($trip->hauler_code) {
                    $byShift[$shiftId]['haulers'][$trip->hauler_code] = true;
                }
            }

            foreach ($byShift as $shiftId => $totals) {
                ProductionRecord::updateOrCreate(
                    [
                        'daily_entry_id' => $dailyEntryId,
                        'pit_id' => $defaultPitId,
                        'shift_id' => $shiftId,
                    ],
                    [
                        'ob_removal_bcm' => round($totals['ob_removal_bcm'], 2),
                        'coal_getting_ton' => round($totals['coal_getting_ton'], 2),
                        'truck_count' => count($totals['haulers']),
                    ],
                );
            }
        });
    }

    public function rollupAll(int $dailyEntryId): void
    {
        $entry = DailyEntry::query()->with('site')->findOrFail($dailyEntryId);
        $this->rollupAfterTripChange($entry);
    }

    public function rollupAfterTripChange(DailyEntry $entry): void
    {
        $this->rollupTripToHourly($entry->id);

        if ($this->shouldAutoPopulateProduction($entry->site->code)) {
            $this->rollupTripToDaily($entry->id);
        }

        $this->calculationService->invalidateSiteCache($entry->site_id, $entry->production_date);
    }

    protected function shouldAutoPopulateProduction(string $siteCode): bool
    {
        return config("mineops.production_source.{$siteCode}", 'parallel') === 'trip_derived';
    }

    /**
     * @return array{trip_ob: float, trip_coal: float, manual_ob: float, manual_coal: float}
     */
    public function reconcile(int $dailyEntryId): array
    {
        $entry = DailyEntry::query()->with('site')->findOrFail($dailyEntryId);
        $coalDensity = $this->coalDensityFactor($entry->site->code);

        $trips = TripProductionRecord::query()
            ->where('daily_entry_id', $dailyEntryId)
            ->get();

        $tripOb = 0.0;
        $tripCoal = 0.0;

        foreach ($trips as $trip) {
            $volume = (float) $trip->volume_bcm;
            if ($trip->material_type === MaterialType::Overburden) {
                $tripOb += $volume;
            } elseif ($trip->material_type === MaterialType::Coal) {
                $tripCoal += $volume * $coalDensity;
            }
        }

        $manual = ProductionRecord::query()
            ->where('daily_entry_id', $dailyEntryId)
            ->selectRaw('COALESCE(SUM(ob_removal_bcm), 0) as ob, COALESCE(SUM(coal_getting_ton), 0) as coal')
            ->first();

        return [
            'trip_ob' => round($tripOb, 2),
            'trip_coal' => round($tripCoal, 2),
            'manual_ob' => round((float) ($manual->ob ?? 0), 2),
            'manual_coal' => round((float) ($manual->coal ?? 0), 2),
        ];
    }

    protected function tripVolumeToTonnage(TripProductionRecord $trip, float $coalDensity): float
    {
        $volume = (float) $trip->volume_bcm;

        return match ($trip->material_type) {
            MaterialType::Coal => $volume * $coalDensity,
            default => $volume,
        };
    }

    protected function coalDensityFactor(string $siteCode): float
    {
        return (float) (config("mineops.coal_density_factor.{$siteCode}")
            ?? config('mineops.coal_density_factor.default', 1.0));
    }
}
