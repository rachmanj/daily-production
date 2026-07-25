<?php

namespace App\Exports\Consolidated;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SummarySheetExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        protected array $data,
    ) {}

    public function collection(): Collection
    {
        return collect($this->data['per_site'])->map(fn (array $row) => [
            'site' => $row['site']->code,
            'ob_bcm' => $row['totals']['ob'],
            'coal_ton' => $row['totals']['coal'],
            'hauling_ton' => $row['totals']['hauling'],
            'fuel_liters' => $row['totals']['fuel_liters'],
            'sr' => $row['totals']['sr'],
        ]);
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Site', 'OB (Bcm)', 'Coal (Ton)', 'Hauling (Ton)', 'Fuel (L)', 'SR'];
    }

    public function title(): string
    {
        return 'Summary';
    }
}
