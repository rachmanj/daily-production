<?php

namespace App\Enums;

enum MaterialType: string
{
    case Limestone = 'limestone';
    case Shalestone = 'shalestone';
    case Coal = 'coal';
    case Overburden = 'ob';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Limestone => 'Limestone (LS)',
            self::Shalestone => 'Shalestone (SH)',
            self::Coal => 'Coal',
            self::Overburden => 'Overburden (OB)',
            self::Other => 'Lainnya',
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
