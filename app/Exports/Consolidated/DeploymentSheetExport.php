<?php

namespace App\Exports\Consolidated;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DeploymentSheetExport implements FromCollection, WithHeadings, WithTitle
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
            foreach ($entry->equipmentDeployments as $record) {
                $rows->push([
                    'date' => $entry->production_date->format('Y-m-d'),
                    'site' => $entry->site->code,
                    'unit' => $record->unit_code,
                    'pit' => $record->pit->code,
                    'shift' => $record->shift->name,
                    'ob_bcm' => $record->prod_ob_bcm,
                    'coal_ton' => $record->prod_coal_ton,
                    'operator' => $record->operator_name,
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
        return ['Date', 'Site', 'Unit', 'PIT', 'Shift', 'OB (Bcm)', 'Coal (Ton)', 'Operator'];
    }

    public function title(): string
    {
        return 'Deployment';
    }
}
