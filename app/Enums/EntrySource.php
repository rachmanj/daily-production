<?php

namespace App\Enums;

enum EntrySource: string
{
    case Manual = 'manual';
    case ExcelImport = 'excel_import';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::ExcelImport => 'Impor Excel',
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
