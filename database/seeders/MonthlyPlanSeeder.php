<?php

namespace Database\Seeders;

use App\Enums\PitOwner;
use App\Enums\PlanMetric;
use App\Models\MonthlyPlan;
use App\Models\Pit;
use App\Models\PlanTarget;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;

class MonthlyPlanSeeder extends Seeder
{
    public function run(): void
    {
        $site = Site::where('code', '022C')->first();
        $admin = User::first();

        if (! $site || ! $admin) {
            return;
        }

        $plan = MonthlyPlan::firstOrCreate(
            [
                'site_id' => $site->id,
                'year' => now()->year,
                'month' => now()->month,
            ],
            ['created_by' => $admin->id],
        );

        $pits = Pit::where('site_id', $site->id)->get();

        foreach ($pits as $pit) {
            PlanTarget::firstOrCreate(
                [
                    'monthly_plan_id' => $plan->id,
                    'pit_id' => $pit->id,
                    'metric' => PlanMetric::OB,
                    'owner' => PitOwner::GPK,
                ],
                ['target_value' => 500000],
            );

            PlanTarget::firstOrCreate(
                [
                    'monthly_plan_id' => $plan->id,
                    'pit_id' => $pit->id,
                    'metric' => PlanMetric::Coal,
                    'owner' => PitOwner::GPK,
                ],
                ['target_value' => 150000],
            );
        }
    }
}
