<?php

namespace Database\Seeders;

use App\Enums\MaterialType;
use App\Models\MaterialDailyPlan;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaterialDailyPlanSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@mineops.test')->first();
        if (! $admin) {
            return;
        }

        $year = (int) now()->year;
        $month = (int) now()->month;

        $site021C = Site::where('code', '021C')->first();
        $site025C = Site::where('code', '025C')->first();

        if ($site021C) {
            $this->seedPlan($site021C->id, MaterialType::Limestone, $year, $month, 10833, 325004, 20, $admin->id);
            $this->seedPlan($site021C->id, MaterialType::Shalestone, $year, $month, 2400, 95000, 20, $admin->id);
        }

        if ($site025C) {
            $this->seedPlan($site025C->id, MaterialType::Limestone, $year, $month, 10833, 325004, 20, $admin->id);
        }
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
