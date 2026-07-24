<?php

namespace App\Jobs;

use App\Imports\DailyInfoImport;
use App\Imports\DprImport;
use App\Imports\FuelReportImport;
use App\Models\ImportBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ParseExcelImportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ImportBatch $batch,
    ) {}

    public function handle(): void
    {
        $path = Storage::path($this->batch->stored_path);
        $import = match ($this->batch->type) {
            'info' => new DailyInfoImport,
            'fuel' => new FuelReportImport,
            default => new DprImport,
        };

        Excel::import($import, $path);

        $this->batch->update([
            'status' => 'preview',
            'parsed_payload' => $import->getParsedData(),
            'row_errors' => $import->getRowErrors(),
        ]);
    }
}
