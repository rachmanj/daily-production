<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $sites = [
            ['code' => '022C', 'name' => 'GPK Project'],
            ['code' => '021C', 'name' => 'SBI Project'],
            ['code' => '017C', 'name' => 'KPUC Project'],
            ['code' => '011C', 'name' => 'Kitadin Project'],
            ['code' => '025C', 'name' => 'SBI Project 2'],
            ['code' => '026C', 'name' => 'CEP Project'],
            ['code' => '023C', 'name' => 'Bharinto Project'],
            ['code' => 'APS', 'name' => 'Arka Project Support'],
        ];

        foreach ($sites as $site) {
            Site::firstOrCreate(
                ['code' => $site['code']],
                ['name' => $site['name'], 'is_active' => true]
            );
        }
    }
}
