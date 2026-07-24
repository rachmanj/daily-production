<?php

namespace App\Enums;

enum FuelUsageCategory: string
{
    case WasteLoading = 'waste_loading';
    case WasteHauling = 'waste_hauling';
    case Dewatering = 'dewatering';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::WasteLoading => 'Waste Loading',
            self::WasteHauling => 'Waste Hauling',
            self::Dewatering => 'Dewatering',
            self::General => 'General & Support',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
