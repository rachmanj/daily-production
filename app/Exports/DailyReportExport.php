<?php

namespace App\Exports;

use App\Models\DailyEntry;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailyReportExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected DailyEntry $entry,
    ) {}

    public function collection()
    {
        $this->entry->load('productionRecords.pit', 'productionRecords.shift');

        return $this->entry->productionRecords->map(fn ($r) => [
            'pit' => $r->pit->code,
            'shift' => $r->shift->name,
            'ob_bcm' => $r->ob_removal_bcm,
            'coal_ton' => $r->coal_getting_ton,
            'coal_hauling' => $r->coal_hauling_ton,
            'trucks' => $r->truck_count,
        ]);
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['PIT', 'Shift', 'OB (Bcm)', 'Coal (Ton)', 'Coal Hauling', 'Truck Count'];
    }
}
