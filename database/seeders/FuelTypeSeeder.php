<?php

namespace Database\Seeders;

use App\Models\FuelType;
use Illuminate\Database\Seeder;

class FuelTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Solar', 'Bio Solar'] as $name) {
            FuelType::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}
