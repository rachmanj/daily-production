<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            SiteSeeder::class,
            PitSeeder::class,
            ShiftSeeder::class,
            FuelTypeSeeder::class,
            FuelPriceSeeder::class,
            ProjectSiteMappingSeeder::class,
            EquipmentAssignmentSeeder::class,
            CcrEquipmentSeeder::class,
            MaterialDailyPlanSeeder::class,
            MonthlyPlanSeeder::class,
            TestUserSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
