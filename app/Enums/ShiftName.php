<?php

namespace App\Enums;

enum ShiftName: string
{
    case Day = 'day';
    case Night = 'night';
    case Day8h = 'day-8h';
    case Swing = 'swing';
    case Graveyard = 'graveyard';

    public function label(): string
    {
        return match ($this) {
            self::Day => 'Siang',
            self::Night => 'Malam',
            self::Day8h => 'Siang 8 Jam',
            self::Swing => 'Swing',
            self::Graveyard => 'Dini Hari',
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
