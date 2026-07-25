<?php

namespace App\Services;

use App\Enums\EntryStatus;
use App\Exports\ConsolidatedReportExport;
use App\Exports\CustomPeriodReportExport;
use App\Exports\DailyReportExport;
use App\Models\DailyEntry;
use App\Models\Site;
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

    /**
     * @param  array<int, int>  $siteIds
     * @return array<string, mixed>
     */
    public function buildConsolidatedReportData(array $siteIds, Carbon $from, Carbon $to): array
    {
        if ($siteIds === []) {
            $siteIds = Site::query()
                ->where('is_active', true)
                ->orderBy('code')
                ->pluck('id')
                ->all();
        }

        $entries = DailyEntry::query()
            ->with([
                'site',
                'productionRecords.pit',
                'productionRecords.shift',
                'fuelRecords.shift',
                'fuelRecords.fuelType',
                'equipmentDeployments.pit',
                'equipmentDeployments.shift',
                'siteInfo',
            ])
            ->whereIn('site_id', $siteIds)
            ->where('status', EntryStatus::Approved)
            ->whereBetween('production_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('site_id')
            ->orderBy('production_date')
            ->get();

        $sites = Site::query()
            ->whereIn('id', $siteIds)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $perSite = [];
        $grandOb = 0.0;
        $grandCoal = 0.0;
        $grandHauling = 0.0;
        $grandFuel = 0.0;

        foreach ($sites as $site) {
            $siteEntries = $entries->where('site_id', $site->id)->values();
            $ob = $this->calculationService->totalForPeriod($site->id, $from, $to, 'ob_removal_bcm');
            $coal = $this->calculationService->totalForPeriod($site->id, $from, $to, 'coal_getting_ton');
            $hauling = $this->calculationService->totalForPeriod($site->id, $from, $to, 'coal_hauling_ton');
            $fuel = $this->calculationService->totalForPeriod($site->id, $from, $to, 'fuel_liters');

            $perSite[] = [
                'site' => $site,
                'entries' => $siteEntries,
                'totals' => [
                    'ob' => $ob,
                    'coal' => $coal,
                    'hauling' => $hauling,
                    'fuel_liters' => $fuel,
                    'sr' => $this->calculationService->strippingRatio($ob, $coal),
                ],
            ];

            $grandOb += $ob;
            $grandCoal += $coal;
            $grandHauling += $hauling;
            $grandFuel += $fuel;
        }

        return [
            'header_code' => 'ARKA/ENG/IV/12.01',
            'generated_at' => now()->format('d/m/Y H:i'),
            'date_from' => $from->format('d/m/Y'),
            'date_to' => $to->format('d/m/Y'),
            'sites' => $sites,
            'per_site' => $perSite,
            'entries' => $entries,
            'totals' => [
                'ob' => $grandOb,
                'coal' => $grandCoal,
                'hauling' => $grandHauling,
                'fuel_liters' => $grandFuel,
                'sr' => $this->calculationService->strippingRatio($grandOb, $grandCoal),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function generateConsolidatedPdf(array $filters): string
    {
        $from = Carbon::parse($filters['date_from'])->startOfDay();
        $to = Carbon::parse($filters['date_to'])->endOfDay();
        $siteIds = $filters['site_ids'] ?? [];

        $data = $this->buildConsolidatedReportData($siteIds, $from, $to);
        $pdf = Pdf::loadView('reports.consolidated', $data);

        $filename = 'consolidated-report-'.now()->format('YmdHis').'.pdf';
        $path = storage_path("app/reports/{$filename}");

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        return $filename;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function generateConsolidatedExcel(array $filters): string
    {
        $from = Carbon::parse($filters['date_from'])->startOfDay();
        $to = Carbon::parse($filters['date_to'])->endOfDay();
        $siteIds = $filters['site_ids'] ?? [];

        $data = $this->buildConsolidatedReportData($siteIds, $from, $to);
        $filename = 'consolidated-report-'.now()->format('YmdHis').'.xlsx';

        Excel::store(new ConsolidatedReportExport($data), "reports/{$filename}");

        return $filename;
    }
}
