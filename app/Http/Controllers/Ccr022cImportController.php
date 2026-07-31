<?php

namespace App\Http\Controllers;

use App\Jobs\CommitCcr022cImportJob;
use App\Jobs\ParseCcr022cImportJob;
use App\Models\DailyEntry;
use App\Models\ImportBatch;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class Ccr022cImportController extends Controller
{
    public function create(): Response
    {
        $this->authorize('create', DailyEntry::class);

        return Inertia::render('ccr-022c/Import', [
            'sites' => Site::query()->where('code', '022C')->get(['id', 'code', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', DailyEntry::class);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
            'site_id' => ['required', 'exists:sites,id'],
        ]);

        $file = $request->file('file');
        $path = $file->store('imports');

        $batch = ImportBatch::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'type' => 'ccr_022c_trip',
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'status' => 'parsing',
        ]);

        ParseCcr022cImportJob::dispatch($batch, (int) $validated['site_id']);

        return redirect()->route('ccr-022c.import.preview', $batch)
            ->with('success', 'File CCR 022C sedang diproses...');
    }

    public function preview(ImportBatch $batch): Response
    {
        return Inertia::render('ccr-022c/Preview', [
            'batch' => $batch->fresh(),
        ]);
    }

    public function confirm(ImportBatch $batch): RedirectResponse
    {
        if ($batch->status !== 'preview') {
            return redirect()->back()->with('error', 'Batch belum siap untuk dikonfirmasi.');
        }

        CommitCcr022cImportJob::dispatch($batch);

        return redirect()->route('hourly.index')
            ->with('success', 'Import CCR 022C sedang dikomit dan di-rollup.');
    }
}
