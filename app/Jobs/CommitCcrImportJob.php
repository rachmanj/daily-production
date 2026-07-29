<?php

namespace App\Jobs;

use App\Enums\MaterialType;
use App\Models\DailyEntry;
use App\Models\ImportBatch;
use App\Services\HourlyProductionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CommitCcrImportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ImportBatch $batch,
    ) {}

    public function handle(HourlyProductionService $hourlyProductionService): void
    {
        $payload = $this->batch->parsed_payload ?? [];

        if (empty($payload['hourly_records'])) {
            $this->batch->update(['status' => 'failed']);

            return;
        }

        $entry = DailyEntry::query()
            ->where('site_id', $payload['site_id'])
            ->whereDate('production_date', $payload['production_date'])
            ->first();

        if (! $entry) {
            $entry = $hourlyProductionService->createEntry([
                'production_date' => $payload['production_date'],
                'site_id' => $payload['site_id'],
            ], $this->batch->user_id);
        }

        $material = MaterialType::from($payload['material_type']);

        $hourlyProductionService->upsertHourlyRecords(
            $entry,
            $material,
            (int) $payload['shift_id'],
            $payload['hourly_records'],
        );

        $this->batch->update(['status' => 'committed']);
    }
}
