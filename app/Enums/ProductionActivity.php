<?php

namespace App\Enums;

enum ProductionActivity: string
{
    case OB = 'ob';
    case Coal = 'coal';
    case TopSoil = 'top_soil';
    case MUD = 'mud';
    case HighAshCoal = 'high_ash_coal';

    public function label(): string
    {
        return match ($this) {
            self::OB => 'OB',
            self::Coal => 'Coal',
            self::TopSoil => 'Top Soil',
            self::MUD => 'MUD',
            self::HighAshCoal => 'High Ash Coal',
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
