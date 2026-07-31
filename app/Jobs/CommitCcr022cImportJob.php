<?php

namespace App\Jobs;

use App\Models\DailyEntry;
use App\Models\ImportBatch;
use App\Services\HourlyProductionService;
use App\Services\TripProductionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CommitCcr022cImportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ImportBatch $batch,
    ) {}

    public function handle(
        HourlyProductionService $hourlyProductionService,
        TripProductionService $tripProductionService,
    ): void {
        $payload = $this->batch->parsed_payload ?? [];

        if (empty($payload['trip_records'])) {
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

        $tripProductionService->upsertTrips($entry, $payload['trip_records'], replaceExisting: true);

        $this->batch->update(['status' => 'committed']);
    }
}
