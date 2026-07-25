<?php

namespace App\Exports\Consolidated;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductionSheetExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        protected array $data,
    ) {}

    public function collection(): Collection
    {
        $rows = collect();

        foreach ($this->data['entries'] as $entry) {
            foreach ($entry->productionRecords as $record) {
                $rows->push([
                    'date' => $entry->production_date->format('Y-m-d'),
                    'site' => $entry->site->code,
                    'pit' => $record->pit->code,
                    'shift' => $record->shift->name,
                    'ob_bcm' => $record->ob_removal_bcm,
                    'coal_ton' => $record->coal_getting_ton,
                    'coal_hauling' => $record->coal_hauling_ton,
                    'trucks' => $record->truck_count,
                ]);
            }
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Date', 'Site', 'PIT', 'Shift', 'OB (Bcm)', 'Coal (Ton)', 'Coal Hauling', 'Truck Count'];
    }

    public function title(): string
    {
        return 'Production';
    }
}
