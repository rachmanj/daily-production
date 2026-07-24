<?php

namespace App\Http\Controllers;

use App\Models\DailyEntry;
use App\Models\Pit;
use App\Models\Site;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('reports/Index', [
            'sites' => Site::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
        ]);
    }

    public function daily(Request $request): BinaryFileResponse|RedirectResponse
    {
        $entry = DailyEntry::query()
            ->where('site_id', $request->integer('site_id'))
            ->whereDate('production_date', $request->string('date'))
            ->first();

        if (! $entry) {
            return redirect()->back()->with('error', 'Entry tidak ditemukan untuk tanggal tersebut.');
        }

        $format = $request->string('format', 'pdf');
        $filename = $format === 'excel'
            ? $this->reportService->generateDailyExcel($entry)
            : $this->reportService->generateDailyPdf($entry);

        return $this->downloadFile($filename);
    }

    public function custom(): Response
    {
        return Inertia::render('reports/Custom', [
            'sites' => Site::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'pits' => Pit::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'site_id']),
        ]);
    }

    public function customGenerate(Request $request): BinaryFileResponse|RedirectResponse
    {
        $filters = $request->validate([
            'site_id' => ['required', 'exists:sites,id'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'pit_id' => ['nullable', 'exists:pits,id'],
            'format' => ['required', 'in:pdf,excel'],
        ]);

        $filename = $filters['format'] === 'excel'
            ? $this->reportService->generateCustomExcel($filters)
            : $this->reportService->generateCustomPdf($filters);

        return $this->downloadFile($filename);
    }

    public function download(string $file): BinaryFileResponse
    {
        $path = storage_path("app/reports/{$file}");
        abort_unless(file_exists($path), 404);

        return response()->download($path);
    }

    protected function downloadFile(string $filename): BinaryFileResponse
    {
        $path = storage_path("app/reports/{$filename}");
        if (! file_exists($path)) {
            $path = Storage::path("reports/{$filename}");
        }

        abort_unless(file_exists($path), 404);

        return response()->download($path);
    }
}
