<?php

namespace App\Jobs;

use App\Enums\EntrySource;
use App\Models\ImportBatch;
use App\Services\DailyEntryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CommitExcelImportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ImportBatch $batch,
    ) {}

    public function handle(DailyEntryService $dailyEntryService): void
    {
        $payload = $this->batch->parsed_payload ?? [];

        if (empty($payload['entries'])) {
            $this->batch->update(['status' => 'failed']);

            return;
        }

        foreach ($payload['entries'] as $entryData) {
            $entry = $dailyEntryService->create([
                'production_date' => $entryData['production_date'],
                'site_id' => $entryData['site_id'],
                'source' => EntrySource::ExcelImport,
                'source_file' => $this->batch->original_filename,
            ], $this->batch->user_id);

            $dailyEntryService->commitImportPayload($entry, $entryData);
        }

        $this->batch->update(['status' => 'committed']);
    }
}
