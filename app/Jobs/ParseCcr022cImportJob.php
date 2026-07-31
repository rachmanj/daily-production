<?php

namespace App\Jobs;

use App\Imports\Ccr022cTripImport;
use App\Models\ImportBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ParseCcr022cImportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ImportBatch $batch,
        public int $siteId,
    ) {}

    public function handle(): void
    {
        $path = Storage::path($this->batch->stored_path);

        $import = new Ccr022cTripImport($this->siteId);

        Excel::import($import, $path);

        $this->batch->update([
            'status' => 'preview',
            'parsed_payload' => $import->getParsedData(),
            'row_errors' => $import->getRowErrors(),
        ]);
    }
}
