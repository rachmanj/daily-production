<?php

namespace Database\Seeders;

use App\Enums\ShiftName;
use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        Shift::firstOrCreate(
            ['name' => ShiftName::Day],
            ['start_time' => '06:00:00', 'end_time' => '18:00:00']
        );

        Shift::firstOrCreate(
            ['name' => ShiftName::Night],
            ['start_time' => '18:00:00', 'end_time' => '06:00:00']
        );
    }
}
