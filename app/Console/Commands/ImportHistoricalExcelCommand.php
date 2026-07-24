<?php

namespace App\Console\Commands;

use App\Jobs\ParseExcelImportJob;
use App\Models\ImportBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportHistoricalExcelCommand extends Command
{
    protected $signature = 'mineops:import-historical {directory} {--type=dpr} {--user=1}';

    protected $description = 'Batch import historical Excel files from a directory';

    public function handle(): int
    {
        $directory = $this->argument('directory');
        $files = glob(rtrim($directory, '/').'/*.{xlsx,xls,csv}', GLOB_BRACE);

        if (empty($files)) {
            $this->error('No Excel files found.');

            return self::FAILURE;
        }

        foreach ($files as $file) {
            $storedPath = 'imports/'.basename($file);
            Storage::put($storedPath, file_get_contents($file));

            $batch = ImportBatch::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => (int) $this->option('user'),
                'type' => $this->option('type'),
                'original_filename' => basename($file),
                'stored_path' => $storedPath,
                'status' => 'parsing',
            ]);

            ParseExcelImportJob::dispatchSync($batch);
            $this->info("Parsed: {$file}");
        }

        return self::SUCCESS;
    }
}
