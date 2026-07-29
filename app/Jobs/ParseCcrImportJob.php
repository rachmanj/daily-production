<?php

namespace App\Jobs;

use App\Imports\CcrHourlyImport;
use App\Models\ImportBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ParseCcrImportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ImportBatch $batch,
        public int $siteId,
        public string $materialType,
        public int $shiftId,
        public string $productionDate,
    ) {}

    public function handle(): void
    {
        $path = Storage::path($this->batch->stored_path);

        $import = new CcrHourlyImport(
            $this->siteId,
            $this->materialType,
            $this->shiftId,
            $this->productionDate,
        );

        Excel::import($import, $path);

        $this->batch->update([
            'status' => 'preview',
            'parsed_payload' => $import->getParsedData(),
            'row_errors' => $import->getRowErrors(),
        ]);
    }
}
