<?php

namespace App\Imports;

use App\Models\EquipmentAssignment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CcrHourlyImport implements ToCollection, WithHeadingRow
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $parsedData = [];

    /**
     * @var array<int, string>
     */
    protected array $rowErrors = [];

    public function __construct(
        protected int $siteId,
        protected string $materialType,
        protected int $shiftId,
        protected string $productionDate,
    ) {}

    public function collection(Collection $rows): void
    {
        $equipmentMap = EquipmentAssignment::query()
            ->where('site_id', $this->siteId)
            ->where('material_type', $this->materialType)
            ->pluck('equipment_id', 'unit_code')
            ->all();

        $records = [];

        foreach ($rows as $index => $row) {
            $hourLabel = $row['jam'] ?? $row['hour'] ?? null;
            if (! $hourLabel) {
                continue;
            }

            $hourSlot = $this->parseHourSlot((string) $hourLabel);
            if ($hourSlot === null) {
                $this->rowErrors[] = "Baris {$index}: format jam tidak valid ({$hourLabel})";

                continue;
            }

            foreach ($row as $column => $value) {
                if (in_array($column, ['jam', 'hour', 'd_shift', 'total'], true)) {
                    continue;
                }

                $unitCode = $this->normalizeUnitCode($column);
                if (! isset($equipmentMap[$unitCode])) {
                    continue;
                }

                $tonnage = (float) ($value ?? 0);
                if ($tonnage <= 0) {
                    continue;
                }

                $records[] = [
                    'equipment_id' => $equipmentMap[$unitCode],
                    'unit_code' => $unitCode,
                    'hour_slot' => $hourSlot,
                    'tonnage' => $tonnage,
                    'shift_id' => $this->shiftId,
                    'material_type' => $this->materialType,
                ];
            }
        }

        $this->parsedData = [
            'production_date' => $this->productionDate,
            'site_id' => $this->siteId,
            'material_type' => $this->materialType,
            'shift_id' => $this->shiftId,
            'hourly_records' => $records,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
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

    protected function parseHourSlot(string $label): ?int
    {
        if (preg_match('/^(\d{1,2})/', trim($label), $matches)) {
            $hour = (int) $matches[1];

            return $hour >= 0 && $hour <= 23 ? $hour : null;
        }

        return null;
    }

    protected function normalizeUnitCode(string $column): string
    {
        $code = strtoupper(str_replace('_', ' ', trim($column)));
        if (preg_match('/^E\s*(\d+)$/', $code, $m)) {
            return 'E '.str_pad($m[1], 3, '0', STR_PAD_LEFT);
        }
        if (preg_match('/^WL\s*(\d+)$/', $code, $m)) {
            return 'WL '.str_pad($m[1], 3, '0', STR_PAD_LEFT);
        }

        return $code;
    }
}
