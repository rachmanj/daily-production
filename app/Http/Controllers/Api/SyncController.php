<?php

namespace App\Http\Controllers\Api;

use App\Enums\MaterialType;
use App\Http\Controllers\Controller;
use App\Models\DailyEntry;
use App\Services\DailyEntryService;
use App\Services\HourlyProductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __construct(
        protected DailyEntryService $dailyEntryService,
        protected HourlyProductionService $hourlyProductionService,
    ) {}

    public function dailyEntries(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entries' => ['required', 'array'],
            'entries.*.uuid' => ['required', 'uuid'],
            'entries.*.production_date' => ['required', 'date'],
            'entries.*.site_id' => ['required', 'exists:sites,id'],
            'entries.*.production' => ['nullable', 'array'],
            'entries.*.fuel' => ['nullable', 'array'],
            'entries.*.deployments' => ['nullable', 'array'],
            'entries.*.site_info' => ['nullable', 'array'],
            'entries.*.hourly' => ['nullable', 'array'],
            'entries.*.hourly.material_type' => ['nullable', 'string'],
            'entries.*.hourly.shift_id' => ['nullable', 'integer'],
            'entries.*.hourly.records' => ['nullable', 'array'],
        ]);

        $results = [];
        foreach ($validated['entries'] as $entryData) {
            $entry = $this->dailyEntryService->create([
                'uuid' => $entryData['uuid'],
                'production_date' => $entryData['production_date'],
                'site_id' => $entryData['site_id'],
            ], $request->user()->id);

            if (! empty($entryData['production'])) {
                $this->dailyEntryService->upsertProductionRecords($entry, $entryData['production']);
            }
            if (! empty($entryData['fuel'])) {
                $this->dailyEntryService->upsertFuelRecords($entry, $entryData['fuel']);
            }
            if (! empty($entryData['deployments'])) {
                $this->dailyEntryService->upsertEquipmentDeployments($entry, $entryData['deployments']);
            }
            if (! empty($entryData['site_info'])) {
                $this->dailyEntryService->upsertSiteInfo($entry, $entryData['site_info']);
            }
            if (! empty($entryData['hourly']['records'])) {
                $this->hourlyProductionService->upsertHourlyRecords(
                    $entry,
                    MaterialType::from($entryData['hourly']['material_type'] ?? MaterialType::Limestone->value),
                    (int) ($entryData['hourly']['shift_id'] ?? 1),
                    $entryData['hourly']['records'],
                );
            }

            $results[] = [
                'uuid' => $entry->uuid,
                'id' => $entry->id,
                'synced' => true,
            ];
        }

        return response()->json(['results' => $results]);
    }

    public function status(Request $request): JsonResponse
    {
        $uuids = $request->validate(['uuids' => ['required', 'array']])['uuids'];

        $synced = DailyEntry::query()
            ->whereIn('uuid', $uuids)
            ->pluck('uuid')
            ->all();

        return response()->json(['synced' => $synced]);
    }
}
