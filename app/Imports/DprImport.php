<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DprImport implements ToCollection
{
    /** @var array<string, mixed> */
    protected array $parsedData = ['entries' => []];

    /** @var array<int, string> */
    protected array $rowErrors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }

            if ($row->filter()->isEmpty()) {
                $this->rowErrors[] = "Baris {$index}: kosong";

                continue;
            }

            $this->parsedData['entries'][] = [
                'production_date' => $row[0] ?? now()->toDateString(),
                'site_id' => 1,
                'production' => [[
                    'pit_id' => 1,
                    'shift_id' => 1,
                    'ob_removal_bcm' => (float) ($row[1] ?? 0),
                    'coal_getting_ton' => (float) ($row[2] ?? 0),
                    'coal_hauling_ton' => (float) ($row[3] ?? 0),
                ]],
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
