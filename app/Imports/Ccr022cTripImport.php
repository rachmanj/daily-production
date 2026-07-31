<?php

namespace App\Imports;

use App\Enums\MaterialType;
use App\Enums\ShiftName;
use App\Models\EquipmentAssignment;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;

class Ccr022cTripImport implements WithMultipleSheets
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $parsedTrips = [];

    /**
     * @var array<int, string>
     */
    protected array $rowErrors = [];

    /**
     * @var array<int, string>
     */
    protected array $unmatchedCodes = [];

    protected int $siteId;

    protected string $productionDate = '';

    /**
     * @var array<string, int>
     */
    protected array $shiftMap = [];

    /**
     * @var array<string, array{equipment_id: int, unit_code: string}>
     */
    protected array $equipmentMap = [];

    public function __construct(protected int $siteIdParam)
    {
        $this->siteId = $siteIdParam;
        $this->shiftMap = Shift::query()
            ->get()
            ->mapWithKeys(fn (Shift $s) => [strtoupper($s->name->value) => $s->id])
            ->all();

        $assignments = EquipmentAssignment::query()
            ->where('site_id', $siteIdParam)
            ->get(['equipment_id', 'unit_code']);

        foreach ($assignments as $assignment) {
            $normalized = $this->normalizeUnitCode($assignment->unit_code);
            $this->equipmentMap[$normalized] = [
                'equipment_id' => $assignment->equipment_id,
                'unit_code' => $assignment->unit_code,
            ];
        }
    }

    /**
     * @return array<string, Ccr022cTripSheetImport>
     */
    public function sheets(): array
    {
        return [
            'DATA TRIP' => new Ccr022cTripSheetImport($this),
        ];
    }

    public function processRow(int $rowIndex, array $row): void
    {
        $excavatorType = $this->stringVal($row[0] ?? null);
        $haulerType = $this->stringVal($row[1] ?? null);
        $capacity = $this->floatVal($row[2] ?? null);
        $volume = $this->floatVal($row[3] ?? null);
        $dateRaw = $row[4] ?? null;
        $shiftLabel = strtoupper($this->stringVal($row[5] ?? null));
        $hourLabel = $this->stringVal($row[6] ?? null);
        $excavatorCode = $this->normalizeUnitCode($this->stringVal($row[7] ?? null));
        $haulerCode = $this->normalizeUnitCode($this->stringVal($row[8] ?? null));
        $tripCount = $this->floatVal($row[9] ?? 1) ?: 1;
        $loadPercent = $this->floatVal($row[10] ?? 100) ?: 100;
        $materialRaw = strtoupper($this->stringVal($row[11] ?? null));

        if (! $excavatorCode && ! $haulerCode && ! $volume) {
            return;
        }

        $productionDate = $this->parseDate($dateRaw);
        if (! $productionDate) {
            $this->rowErrors[] = "Baris {$rowIndex}: tanggal tidak valid";

            return;
        }

        if ($this->productionDate === '') {
            $this->productionDate = $productionDate;
        }

        $hourSlot = $this->parseHourSlot($hourLabel);
        if ($hourSlot === null) {
            $this->rowErrors[] = "Baris {$rowIndex}: format jam tidak valid ({$hourLabel})";

            return;
        }

        $material = $this->mapMaterial($materialRaw);
        if ($material === null) {
            $this->rowErrors[] = "Baris {$rowIndex}: material tidak dikenali ({$materialRaw})";

            return;
        }

        $shiftId = $this->resolveShiftId($shiftLabel);
        if (! $shiftId) {
            $this->rowErrors[] = "Baris {$rowIndex}: shift tidak valid ({$shiftLabel})";

            return;
        }

        $excavatorMatch = $this->equipmentMap[$excavatorCode] ?? null;
        $haulerMatch = $this->equipmentMap[$haulerCode] ?? null;

        if (! $excavatorMatch && $excavatorCode) {
            $this->unmatchedCodes[$excavatorCode] = $excavatorCode;
        }
        if (! $haulerMatch && $haulerCode) {
            $this->unmatchedCodes[$haulerCode] = $haulerCode;
        }

        $this->parsedTrips[] = [
            'excavator_id' => $excavatorMatch['equipment_id'] ?? null,
            'excavator_code' => $excavatorCode ?: null,
            'hauler_id' => $haulerMatch['equipment_id'] ?? null,
            'hauler_code' => $haulerCode ?: null,
            'shift_id' => $shiftId,
            'material_type' => $material->value,
            'hour_slot' => $hourSlot,
            'truck_capacity_bcm' => $capacity,
            'volume_bcm' => $volume,
            'load_percent' => $loadPercent,
            'trip_count' => $tripCount,
            'excavator_type' => $excavatorType,
            'hauler_type' => $haulerType,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getParsedData(): array
    {
        $obTotal = 0.0;
        $coalTotal = 0.0;

        foreach ($this->parsedTrips as $trip) {
            if ($trip['material_type'] === MaterialType::Overburden->value) {
                $obTotal += (float) $trip['volume_bcm'];
            } elseif ($trip['material_type'] === MaterialType::Coal->value) {
                $coalTotal += (float) $trip['volume_bcm'];
            }
        }

        return [
            'site_id' => $this->siteId,
            'production_date' => $this->productionDate,
            'trip_records' => $this->parsedTrips,
            'preview' => [
                'trip_count' => count($this->parsedTrips),
                'ob_bcm' => round($obTotal, 2),
                'coal_ton' => round($coalTotal, 2),
            ],
            'unmatched_codes' => array_values($this->unmatchedCodes),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function getRowErrors(): array
    {
        return $this->rowErrors;
    }

    protected function mapMaterial(string $raw): ?MaterialType
    {
        return match (true) {
            $raw === 'OB' || str_contains($raw, 'OVERBURDEN') => MaterialType::Overburden,
            str_contains($raw, 'TOP SOIL') || $raw === 'TS' => MaterialType::TopSoil,
            $raw === 'COAL' => MaterialType::Coal,
            default => null,
        };
    }

    protected function resolveShiftId(string $label): ?int
    {
        if (str_contains($label, 'NIGHT') || $label === 'MALAM') {
            return $this->shiftMap[strtoupper(ShiftName::Night->value)] ?? null;
        }

        if (str_contains($label, 'DAY') || $label === 'SIANG') {
            return $this->shiftMap[strtoupper(ShiftName::Day->value)] ?? null;
        }

        return $this->shiftMap[$label] ?? null;
    }

    protected function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestampUTC(((float) $value - 25569) * 86400)
                ->format('Y-m-d');
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    protected function parseHourSlot(?string $label): ?int
    {
        if (! $label) {
            return null;
        }

        if (preg_match('/^(\d{1,2})/', trim($label), $matches)) {
            $hour = (int) $matches[1];

            return $hour >= 0 && $hour <= 23 ? $hour : null;
        }

        return null;
    }

    protected function normalizeUnitCode(string $code): string
    {
        $code = strtoupper(trim(preg_replace('/\s+/', ' ', $code) ?? ''));

        if (preg_match('/^E\s*(\d+)$/', $code, $m)) {
            return 'E '.str_pad($m[1], 3, '0', STR_PAD_LEFT);
        }
        if (preg_match('/^ADT\s*(\d+)$/', $code, $m)) {
            return 'ADT '.str_pad($m[1], 3, '0', STR_PAD_LEFT);
        }
        if (preg_match('/^RD\s*(\d+)$/', $code, $m)) {
            return 'RD '.str_pad($m[1], 3, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    protected function stringVal(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }

    protected function floatVal(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) $value;
    }
}

class Ccr022cTripSheetImport implements ToCollection, WithStartRow
{
    public function __construct(protected Ccr022cTripImport $parent) {}

    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $this->parent->processRow($index + 2, $row->toArray());
        }
    }
}
