<?php

namespace Database\Seeders;

use App\Enums\MaterialType;
use App\Models\MaterialDailyPlan;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaterialDailyPlan022cSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@mineops.test')->first();
        $site = Site::where('code', '022C')->first();

        if (! $admin || ! $site) {
            return;
        }

        $year = (int) now()->year;
        $month = (int) now()->month;

        $this->seedPlan($site->id, MaterialType::Overburden, $year, $month, 33483, 1000000, 20, $admin->id);
        $this->seedPlan($site->id, MaterialType::Coal, $year, $month, 10591, 300000, 20, $admin->id);
        $this->seedPlan($site->id, MaterialType::TopSoil, $year, $month, 100, 3000, 20, $admin->id);
    }

    protected function seedPlan(
        int $siteId,
        MaterialType $material,
        int $year,
        int $month,
        float $dailyPlan,
        float $monthlyPlan,
        float $operatingHours,
        int $createdBy,
    ): void {
        MaterialDailyPlan::firstOrCreate(
            [
                'site_id' => $siteId,
                'material_type' => $material,
                'year' => $year,
                'month' => $month,
            ],
            [
                'daily_plan_tonnage' => $dailyPlan,
                'monthly_plan_tonnage' => $monthlyPlan,
                'operating_hours_per_day' => $operatingHours,
                'created_by' => $createdBy,
            ]
        );
    }
}
