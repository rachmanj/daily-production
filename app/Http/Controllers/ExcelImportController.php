<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExcelImportRequest;
use App\Jobs\CommitExcelImportJob;
use App\Jobs\ParseExcelImportJob;
use App\Models\DailyEntry;
use App\Models\ImportBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ExcelImportController extends Controller
{
    public function create(): Response
    {
        $this->authorize('create', DailyEntry::class);

        return Inertia::render('excel-imports/Create');
    }

    public function store(ExcelImportRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('imports');

        $batch = ImportBatch::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'type' => $request->input('type', 'dpr'),
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'status' => 'parsing',
        ]);

        ParseExcelImportJob::dispatch($batch);

        return redirect()->route('excel-imports.preview', $batch)
            ->with('success', 'File sedang diproses...');
    }

    public function preview(ImportBatch $batch): Response
    {
        return Inertia::render('excel-imports/Preview', [
            'batch' => $batch->fresh(),
        ]);
    }

    public function confirm(ImportBatch $batch): RedirectResponse
    {
        if ($batch->status !== 'preview') {
            return redirect()->back()->with('error', 'Batch belum siap untuk dikonfirmasi.');
        }

        CommitExcelImportJob::dispatch($batch);

        return redirect()->route('daily-entries.index')
            ->with('success', 'Import sedang dikomit ke database.');
    }
}
