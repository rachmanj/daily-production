<?php

namespace App\Enums;

enum PitOwner: string
{
    case GPK = 'gpk';
    case ARKA = 'arka';

    public function label(): string
    {
        return match ($this) {
            self::GPK => 'GPK',
            self::ARKA => 'ARKA',
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
