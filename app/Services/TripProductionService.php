<?php

namespace App\Services;

use App\Enums\EntryStatus;
use App\Models\DailyEntry;
use App\Models\TripProductionRecord;
use Illuminate\Support\Facades\DB;

class TripProductionService
{
    public function __construct(
        protected TripAggregationService $tripAggregationService,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $trips
     */
    public function upsertTrips(DailyEntry $entry, array $trips, bool $replaceExisting = false): int
    {
        if ($entry->status !== EntryStatus::Draft && $replaceExisting) {
            return 0;
        }

        $count = 0;

        DB::transaction(function () use ($entry, $trips, $replaceExisting, &$count) {
            if ($replaceExisting) {
                TripProductionRecord::query()
                    ->where('daily_entry_id', $entry->id)
                    ->delete();
            }

            foreach ($trips as $trip) {
                TripProductionRecord::create([
                    'daily_entry_id' => $entry->id,
                    'excavator_id' => $trip['excavator_id'] ?? null,
                    'excavator_code' => $trip['excavator_code'] ?? null,
                    'hauler_id' => $trip['hauler_id'] ?? null,
                    'hauler_code' => $trip['hauler_code'] ?? null,
                    'shift_id' => $trip['shift_id'],
                    'material_type' => $trip['material_type'],
                    'hour_slot' => $trip['hour_slot'],
                    'truck_capacity_bcm' => $trip['truck_capacity_bcm'] ?? 0,
                    'volume_bcm' => $trip['volume_bcm'] ?? 0,
                    'load_percent' => $trip['load_percent'] ?? 100,
                    'trip_count' => $trip['trip_count'] ?? 1,
                    'excavator_type' => $trip['excavator_type'] ?? null,
                    'hauler_type' => $trip['hauler_type'] ?? null,
                ]);
                $count++;
            }

            $entry->loadMissing('site');
            $this->tripAggregationService->rollupAfterTripChange($entry);
        });

        return $count;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPairing(int $dailyEntryId): array
    {
        $rows = TripProductionRecord::query()
            ->where('daily_entry_id', $dailyEntryId)
            ->selectRaw('
                excavator_id,
                excavator_code,
                hauler_id,
                hauler_code,
                SUM(trip_count) as trip_count,
                SUM(volume_bcm) as total_volume,
                AVG(load_percent) as avg_load_percent
            ')
            ->groupBy('excavator_id', 'excavator_code', 'hauler_id', 'hauler_code')
            ->orderBy('excavator_code')
            ->orderBy('hauler_code')
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $excKey = $row->excavator_code ?? 'unknown';

            if (! isset($grouped[$excKey])) {
                $grouped[$excKey] = [
                    'excavator_id' => $row->excavator_id,
                    'excavator_code' => $row->excavator_code,
                    'haulers' => [],
                    'total_trips' => 0,
                    'total_volume' => 0.0,
                ];
            }

            $grouped[$excKey]['haulers'][] = [
                'hauler_id' => $row->hauler_id,
                'hauler_code' => $row->hauler_code,
                'trip_count' => (float) $row->trip_count,
                'total_volume' => round((float) $row->total_volume, 2),
                'avg_load_percent' => round((float) $row->avg_load_percent, 2),
            ];

            $grouped[$excKey]['total_trips'] += (float) $row->trip_count;
            $grouped[$excKey]['total_volume'] += (float) $row->total_volume;
        }

        foreach ($grouped as &$exc) {
            $exc['total_volume'] = round($exc['total_volume'], 2);
        }

        return array_values($grouped);
    }
}
