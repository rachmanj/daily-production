<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Supervisor = 'supervisor';
    case Management = 'management';
    case FuelOfficer = 'fuel_officer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Supervisor => 'Supervisor',
            self::Management => 'Management',
            self::FuelOfficer => 'Fuel Officer',
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
