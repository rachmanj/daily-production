<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class FuelReportImport implements ToCollection
{
    /** @var array<string, mixed> */
    protected array $parsedData = ['entries' => []];

    /** @var array<int, string> */
    protected array $rowErrors = [];

    public function collection(Collection $rows): void
    {
        $grouped = [];

        foreach ($rows as $index => $row) {
            if ($index === 0 || $row->filter()->isEmpty()) {
                continue;
            }

            $date = $row[0] ?? now()->toDateString();
            if (! isset($grouped[$date])) {
                $grouped[$date] = [
                    'production_date' => $date,
                    'site_id' => 1,
                    'fuel' => [],
                ];
            }

            $grouped[$date]['fuel'][] = [
                'equipment_id' => (int) ($row[1] ?? 0),
                'unit_code' => $row[2] ?? '',
                'shift_id' => (int) ($row[3] ?? 1),
                'fuel_type_id' => (int) ($row[4] ?? 1),
                'liters' => (float) ($row[5] ?? 0),
                'working_hours' => (float) ($row[6] ?? 0),
                'usage_category' => $row[7] ?? 'general',
            ];
        }

        $this->parsedData['entries'] = array_values($grouped);
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
