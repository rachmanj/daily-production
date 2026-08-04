<?php

use App\Enums\PlanMetric;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('plan_targets')
            ->where('metric', 'ob_bcm')
            ->update(['metric' => PlanMetric::OB->value]);

        DB::table('plan_targets')
            ->where('metric', 'coal_ton')
            ->update(['metric' => PlanMetric::Coal->value]);

        $validMetrics = array_map(
            fn (PlanMetric $metric) => $metric->value,
            PlanMetric::cases(),
        );

        DB::table('plan_targets')
            ->whereNotIn('metric', $validMetrics)
            ->delete();
    }

    public function down(): void
    {
        // Irreversible data cleanup — production_ton rows were CCR material plans
        // duplicated in plan_targets instead of material_daily_plans.
    }
};
