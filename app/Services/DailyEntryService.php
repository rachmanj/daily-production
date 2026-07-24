<?php

namespace App\Services;

use App\Enums\EntrySource;
use App\Enums\EntryStatus;
use App\Models\DailyEntry;
use App\Models\EquipmentDeployment;
use App\Models\FuelRecord;
use App\Models\ProductionRecord;
use App\Models\SiteInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DailyEntryService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $userId): DailyEntry
    {
        return DB::transaction(function () use ($data, $userId) {
            if (! empty($data['uuid'])) {
                $existing = DailyEntry::query()->where('uuid', $data['uuid'])->first();
                if ($existing) {
                    return $existing;
                }
            }

            return DailyEntry::create([
                'uuid' => $data['uuid'] ?? (string) Str::uuid(),
                'production_date' => $data['production_date'],
                'site_id' => $data['site_id'],
                'created_by' => $userId,
                'status' => EntryStatus::Draft,
                'source' => $data['source'] ?? EntrySource::Manual,
                'source_file' => $data['source_file'] ?? null,
            ]);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     */
    public function upsertProductionRecords(DailyEntry $entry, array $records): void
    {
        DB::transaction(function () use ($entry, $records) {
            $entry->productionRecords()->delete();

            foreach ($records as $record) {
                $entry->productionRecords()->create($record);
            }
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     */
    public function upsertFuelRecords(DailyEntry $entry, array $records): void
    {
        DB::transaction(function () use ($entry, $records) {
            $entry->fuelRecords()->delete();

            foreach ($records as $record) {
                $entry->fuelRecords()->create($record);
            }
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     */
    public function upsertEquipmentDeployments(DailyEntry $entry, array $records): void
    {
        DB::transaction(function () use ($entry, $records) {
            $entry->equipmentDeployments()->delete();

            foreach ($records as $record) {
                $entry->equipmentDeployments()->create($record);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function upsertSiteInfo(DailyEntry $entry, array $data): SiteInfo
    {
        return DB::transaction(function () use ($entry, $data) {
            return $entry->siteInfo()->updateOrCreate(
                ['daily_entry_id' => $entry->id],
                $data,
            );
        });
    }

    public function submit(DailyEntry $entry): DailyEntry
    {
        $entry->update([
            'status' => EntryStatus::Submitted,
            'submitted_at' => now(),
        ]);

        return $entry->fresh();
    }

    public function approve(DailyEntry $entry, int $approverId, CalculationService $calculationService): DailyEntry
    {
        $entry->update([
            'status' => EntryStatus::Approved,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        $calculationService->invalidateSiteCache($entry->site_id, $entry->production_date);

        return $entry->fresh();
    }

    public function reject(DailyEntry $entry): DailyEntry
    {
        $entry->update([
            'status' => EntryStatus::Draft,
            'submitted_at' => null,
        ]);

        return $entry->fresh();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function commitImportPayload(DailyEntry $entry, array $payload): void
    {
        DB::transaction(function () use ($entry, $payload) {
            if (! empty($payload['production'])) {
                foreach ($payload['production'] as $row) {
                    ProductionRecord::create(array_merge($row, ['daily_entry_id' => $entry->id]));
                }
            }
            if (! empty($payload['fuel'])) {
                foreach ($payload['fuel'] as $row) {
                    FuelRecord::create(array_merge($row, ['daily_entry_id' => $entry->id]));
                }
            }
            if (! empty($payload['deployments'])) {
                foreach ($payload['deployments'] as $row) {
                    EquipmentDeployment::create(array_merge($row, ['daily_entry_id' => $entry->id]));
                }
            }
            if (! empty($payload['site_info'])) {
                SiteInfo::updateOrCreate(
                    ['daily_entry_id' => $entry->id],
                    $payload['site_info'],
                );
            }
        });
    }
}
