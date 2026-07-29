<?php

namespace App\Exports;

use App\Enums\EntryStatus;
use App\Enums\MaterialType;
use App\Models\HourlyProductionRecord;
use App\Models\Site;
use App\Services\HourlyProductionService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HourlyProductionExport implements FromArray, WithHeadings, WithTitle
{
    /**
     * @var array<int, array<int, string|float>>
     */
    protected array $rows = [];

    /**
     * @var array<int, string>
     */
    protected array $headings = [];

    public function __construct(
        protected int $siteId,
        protected Carbon $date,
        protected MaterialType $material,
        protected HourlyProductionService $hourlyProductionService,
    ) {
        $this->buildGrid();
    }

    public function download(): BinaryFileResponse
    {
        $site = Site::findOrFail($this->siteId);
        $filename = "ccr-hourly-{$site->code}-{$this->date->format('Y-m-d')}-{$this->material->value}.xlsx";

        return Excel::download($this, $filename);
    }

    public function array(): array
    {
        return $this->rows;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return 'CCR Hourly';
    }

    protected function buildGrid(): void
    {
        $equipment = $this->hourlyProductionService->getEquipmentGrid($this->siteId, $this->material);

        $this->headings = array_merge(
            ['Jam'],
            array_map(fn ($e) => $e['unit_code'], $equipment),
            ['D/Shift'],
        );

        $records = HourlyProductionRecord::query()
            ->where('material_type', $this->material->value)
            ->whereHas('dailyEntry', fn ($q) => $q
                ->where('site_id', $this->siteId)
                ->whereDate('production_date', $this->date)
                ->where('status', EntryStatus::Approved))
            ->get();

        $grid = [];
        foreach ($records as $record) {
            $grid[$record->hour_slot][$record->equipment_id] = (float) $record->tonnage;
        }

        $columnTotals = array_fill_keys(array_column($equipment, 'equipment_id'), 0.0);

        for ($hour = 0; $hour < 24; $hour++) {
            $rowTotal = 0.0;
            $row = [sprintf('%02d:00–%02d:00', $hour, ($hour + 1) % 24)];

            foreach ($equipment as $eq) {
                $value = $grid[$hour][$eq['equipment_id']] ?? 0.0;
                $row[] = $value;
                $rowTotal += $value;
                $columnTotals[$eq['equipment_id']] += $value;
            }

            $row[] = $rowTotal;
            $this->rows[] = $row;
        }

        $totalRow = ['TOTAL ALAT'];
        $grandTotal = 0.0;
        foreach ($equipment as $eq) {
            $totalRow[] = $columnTotals[$eq['equipment_id']];
            $grandTotal += $columnTotals[$eq['equipment_id']];
        }
        $totalRow[] = $grandTotal;
        $this->rows[] = $totalRow;
    }
}
