<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DailyInfoImport implements ToCollection
{
    /** @var array<string, mixed> */
    protected array $parsedData = ['entries' => []];

    /** @var array<int, string> */
    protected array $rowErrors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            if ($index === 0 || $row->filter()->isEmpty()) {
                continue;
            }

            $this->parsedData['entries'][] = [
                'production_date' => $row[0] ?? now()->toDateString(),
                'site_id' => 1,
                'site_info' => [
                    'weather' => $row[1] ?? null,
                    'rain_hours' => (float) ($row[2] ?? 0),
                    'slippery_hours' => (float) ($row[3] ?? 0),
                    'manpower_plan' => (int) ($row[4] ?? 0),
                    'manpower_actual' => (int) ($row[5] ?? 0),
                    'safety_notes' => $row[6] ?? null,
                    'fuel_stock_liters' => (float) ($row[7] ?? 0),
                ],
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getParsedData(): array
    {
        return $this->parsedData;
    }

    /**
     * @return array<int, string>
     */
    public function getRowErrors(): array
    {
        return $this->rowErrors;
    }
}
