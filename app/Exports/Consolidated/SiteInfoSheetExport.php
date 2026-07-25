<?php

namespace App\Exports\Consolidated;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SiteInfoSheetExport implements FromCollection, WithHeadings, WithTitle
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
            if (! $entry->siteInfo) {
                continue;
            }

            $info = $entry->siteInfo;
            $rows->push([
                'date' => $entry->production_date->format('Y-m-d'),
                'site' => $entry->site->code,
                'weather' => $info->weather,
                'rain_hours' => $info->rain_hours,
                'slippery_hours' => $info->slippery_hours,
                'manpower_plan' => $info->manpower_plan,
                'manpower_actual' => $info->manpower_actual,
                'fuel_stock_liters' => $info->fuel_stock_liters,
                'safety_notes' => $info->safety_notes,
            ]);
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Date',
            'Site',
            'Weather',
            'Rain Hours',
            'Slippery Hours',
            'Manpower Plan',
            'Manpower Actual',
            'Fuel Stock (L)',
            'Safety Notes',
        ];
    }

    public function title(): string
    {
        return 'Site Info';
    }
}
