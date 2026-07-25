<?php

namespace App\Exports\Consolidated;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FuelSheetExport implements FromCollection, WithHeadings, WithTitle
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
            foreach ($entry->fuelRecords as $record) {
                $rows->push([
                    'date' => $entry->production_date->format('Y-m-d'),
                    'site' => $entry->site->code,
                    'unit' => $record->unit_code,
                    'fuel_type' => $record->fuelType?->name,
                    'shift' => $record->shift->name,
                    'liters' => $record->liters,
                    'hours' => $record->working_hours,
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
        return ['Date', 'Site', 'Unit', 'Fuel Type', 'Shift', 'Liters', 'Working Hours'];
    }

    public function title(): string
    {
        return 'Fuel';
    }
}
