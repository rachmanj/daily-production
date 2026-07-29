<?php

namespace App\Services;

use App\Enums\EntrySource;
use App\Enums\EntryStatus;
use App\Enums\MaterialType;
use App\Models\DailyEntry;
use App\Models\EquipmentAssignment;
use App\Models\HourlyProductionRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HourlyProductionService
{
    public function __construct(
        protected DailyEntryService $dailyEntryService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createEntry(array $data, int $userId): DailyEntry
    {
        return $this->dailyEntryService->create([
            'production_date' => $data['production_date'],
            'site_id' => $data['site_id'],
            'uuid' => $data['uuid'] ?? (string) Str::uuid(),
            'source' => $data['source'] ?? EntrySource::Manual,
        ], $userId);
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     */
    public function upsertHourlyRecords(
        DailyEntry $entry,
        MaterialType $material,
        int $shiftId,
        array $records,
    ): void {
        if ($entry->status !== EntryStatus::Draft) {
            return;
        }

        DB::transaction(function () use ($entry, $material, $shiftId, $records) {
            foreach ($records as $record) {
                $tonnage = (float) ($record['tonnage'] ?? 0);
                if ($tonnage <= 0 && empty($record['location']) && empty($record['loader_info'])) {
                    HourlyProductionRecord::query()
                        ->where('daily_entry_id', $entry->id)
                        ->where('equipment_id', $record['equipment_id'])
                        ->where('material_type', $material->value)
                        ->where('hour_slot', $record['hour_slot'])
                        ->delete();

                    continue;
                }

                $assignment = EquipmentAssignment::query()
                    ->where('site_id', $entry->site_id)
                    ->where('equipment_id', $record['equipment_id'])
                    ->first();

                HourlyProductionRecord::updateOrCreate(
                    [
                        'daily_entry_id' => $entry->id,
                        'equipment_id' => $record['equipment_id'],
                        'material_type' => $material->value,
                        'hour_slot' => $record['hour_slot'],
                    ],
                    [
                        'unit_code' => $record['unit_code'] ?? $assignment?->unit_code,
                        'shift_id' => $shiftId,
                        'pit_id' => $record['pit_id'] ?? $assignment?->pit_id,
                        'tonnage' => $tonnage,
                        'location' => $record['location'] ?? null,
                        'loader_info' => $record['loader_info'] ?? null,
                    ],
                );
            }
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecordsForEntry(DailyEntry $entry, MaterialType $material): array
    {
        return HourlyProductionRecord::query()
            ->where('daily_entry_id', $entry->id)
            ->where('material_type', $material->value)
            ->orderBy('hour_slot')
            ->get()
            ->map(fn (HourlyProductionRecord $r) => [
                'id' => $r->id,
                'equipment_id' => $r->equipment_id,
                'unit_code' => $r->unit_code,
                'shift_id' => $r->shift_id,
                'hour_slot' => $r->hour_slot,
                'tonnage' => (float) $r->tonnage,
                'location' => $r->location,
                'loader_info' => $r->loader_info,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEquipmentGrid(int $siteId, MaterialType $material): array
    {
        return EquipmentAssignment::query()
            ->where('site_id', $siteId)
            ->where('is_active_for_tracking', true)
            ->where('material_type', $material->value)
            ->orderBy('display_order')
            ->orderBy('unit_code')
            ->get(['id', 'equipment_id', 'unit_code', 'equipment_role', 'display_order', 'plant_type_name'])
            ->map(fn (EquipmentAssignment $a) => [
                'assignment_id' => $a->id,
                'equipment_id' => $a->equipment_id,
                'unit_code' => $a->unit_code,
                'equipment_role' => $a->equipment_role,
                'display_order' => $a->display_order,
                'plant_type_name' => $a->plant_type_name,
            ])
            ->all();
    }

    /**
     * @return array<string, int>
     */
    public function getFleetStatus(int $siteId, MaterialType $material): array
    {
        $assignments = EquipmentAssignment::query()
            ->where('site_id', $siteId)
            ->where('is_active_for_tracking', true)
            ->where('material_type', $material->value)
            ->get();

        $counts = [];
        foreach ($assignments as $assignment) {
            $role = $assignment->equipment_role ?? 'other';
            $counts[$role] = ($counts[$role] ?? 0) + 1;
        }

        return $counts;
    }
}
