<?php

use App\Enums\ProductionActivity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $validActivities = array_map(
            fn (ProductionActivity $activity) => $activity->value,
            ProductionActivity::cases(),
        );

        DB::table('production_records')
            ->whereNotNull('activity')
            ->whereNotIn('activity', $validActivities)
            ->delete();
    }

    public function down(): void
    {
        // Irreversible data cleanup — invalid rows were CCR material summaries
        // incorrectly stored in production_records instead of hourly_production_records.
    }
};
