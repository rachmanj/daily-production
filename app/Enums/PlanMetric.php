<?php

namespace App\Enums;

enum PlanMetric: string
{
    case OB = 'ob';
    case Coal = 'coal';
    case StrippingRatio = 'stripping_ratio';

    public function label(): string
    {
        return match ($this) {
            self::OB => 'OB',
            self::Coal => 'Coal',
            self::StrippingRatio => 'Stripping Ratio',
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
