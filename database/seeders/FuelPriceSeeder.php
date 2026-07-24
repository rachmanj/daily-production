<?php

namespace Database\Seeders;

use App\Models\FuelPrice;
use App\Models\FuelType;
use Illuminate\Database\Seeder;

class FuelPriceSeeder extends Seeder
{
    public function run(): void
    {
        $solar = FuelType::where('name', 'Solar')->first();

        if (! $solar) {
            return;
        }

        FuelPrice::firstOrCreate(
            [
                'fuel_type_id' => $solar->id,
                'effective_date' => '2026-01-01',
            ],
            ['price_per_liter' => 15000]
        );
    }
}
