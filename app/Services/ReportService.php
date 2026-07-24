<?php

namespace App\Services;

use App\Exports\CustomPeriodReportExport;
use App\Exports\DailyReportExport;
use App\Models\DailyEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportService
{
    public function __construct(
        protected CalculationService $calculationService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildDailyReportData(DailyEntry $entry): array
    {
        $entry->load([
            'site',
            'productionRecords.pit',
            'productionRecords.shift',
            'fuelRecords.shift',
            'fuelRecords.fuelType',
            'equipmentDeployments.pit',
            'equipmentDeployments.shift',
            'siteInfo',
            'creator',
            'approver',
        ]);

        $date = Carbon::parse($entry->production_date);

        return [
            'entry' => $entry,
            'header_code' => 'ARKA/ENG/IV/12.01',
            'generated_at' => now()->format('d/m/Y H:i'),
            'mtd_ob' => $this->calculationService->mtd($entry->site_id, $date, 'ob_removal_bcm'),
            'mtd_coal' => $this->calculationService->mtd($entry->site_id, $date, 'coal_getting_ton'),
            'sr' => $this->calculationService->siteStrippingRatio($entry->site_id, $date),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function generateDailyReport(DailyEntry $dailyEntry): array
    {
        return $this->buildDailyReportData($dailyEntry);
    }

    public function generateDailyPdf(DailyEntry $entry): string
    {
        $data = $this->buildDailyReportData($entry);
        $pdf = Pdf::loadView('reports.daily', $data);

        $filename = "daily-report-{$entry->site->code}-{$entry->production_date->format('Y-m-d')}.pdf";
        $path = storage_path("app/reports/{$filename}");

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        return $filename;
    }

    public function generateDailyExcel(DailyEntry $entry): string
    {
        $filename = "daily-report-{$entry->site->code}-{$entry->production_date->format('Y-m-d')}.xlsx";
        $path = "reports/{$filename}";

        Excel::store(new DailyReportExport($entry), $path);

        return $filename;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function generateCustomExcel(array $filters): string
    {
        $filename = 'custom-report-'.now()->format('YmdHis').'.xlsx';
        Excel::store(new CustomPeriodReportExport($filters), "reports/{$filename}");

        return $filename;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function generateCustomPdf(array $filters): string
    {
        $data = ['filters' => $filters, 'header_code' => 'ARKA/ENG/IV/12.01'];
        $pdf = Pdf::loadView('reports.custom', $data);
        $filename = 'custom-report-'.now()->format('YmdHis').'.pdf';
        $path = storage_path("app/reports/{$filename}");

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        return $filename;
    }
}
