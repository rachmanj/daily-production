<?php

namespace App\Exports;

use App\Enums\EntryStatus;
use App\Models\ProductionRecord;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomPeriodReportExport implements FromQuery, WithHeadings
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        protected array $filters,
    ) {}

    public function query()
    {
        $query = ProductionRecord::query()
            ->with(['pit', 'shift', 'dailyEntry.site'])
            ->whereHas('dailyEntry', function ($q) {
                $q->where('site_id', $this->filters['site_id'])
                    ->where('status', EntryStatus::Approved)
                    ->whereBetween('production_date', [
                        $this->filters['date_from'],
                        $this->filters['date_to'],
                    ]);
            });

        if (! empty($this->filters['pit_id'])) {
            $query->where('pit_id', $this->filters['pit_id']);
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Date', 'Site', 'PIT', 'Shift', 'OB (Bcm)', 'Coal (Ton)', 'Coal Hauling'];
    }
}
