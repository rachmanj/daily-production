<?php

namespace Database\Seeders;

use App\Enums\EntryStatus;
use App\Enums\FuelUsageCategory;
use App\Models\DailyEntry;
use App\Models\EquipmentAssignment;
use App\Models\EquipmentDeployment;
use App\Models\FuelRecord;
use App\Models\FuelType;
use App\Models\Pit;
use App\Models\ProductionRecord;
use App\Models\Shift;
use App\Models\Site;
use App\Models\SiteInfo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $site = Site::where('code', '022C')->first();
        $admin = User::first();
        $pits = Pit::where('site_id', $site?->id)->get();
        $shifts = Shift::all();
        $fuelType = FuelType::first();
        $equipment = EquipmentAssignment::where('site_id', $site?->id)->limit(5)->get();

        if (! $site || ! $admin || $pits->isEmpty() || $shifts->isEmpty()) {
            return;
        }

        $start = Carbon::create(2026, 5, 1);
        $end = Carbon::create(2026, 5, 31);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $entry = DailyEntry::firstOrCreate(
                [
                    'production_date' => $date->toDateString(),
                    'site_id' => $site->id,
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                    'status' => EntryStatus::Approved,
                    'submitted_at' => $date->copy()->setTime(17, 0),
                    'approved_at' => $date->copy()->setTime(18, 0),
                ],
            );

            if ($entry->wasRecentlyCreated) {
                foreach ($pits as $pit) {
                    foreach ($shifts as $shift) {
                        ProductionRecord::create([
                            'daily_entry_id' => $entry->id,
                            'pit_id' => $pit->id,
                            'shift_id' => $shift->id,
                            'ob_removal_bcm' => rand(8000, 15000),
                            'coal_getting_ton' => rand(2000, 5000),
                            'coal_hauling_ton' => rand(1500, 4000),
                            'truck_count' => rand(10, 25),
                        ]);
                    }
                }

                foreach ($equipment as $eq) {
                    FuelRecord::create([
                        'daily_entry_id' => $entry->id,
                        'equipment_id' => $eq->equipment_id,
                        'unit_code' => $eq->unit_code,
                        'shift_id' => $shifts->first()->id,
                        'fuel_type_id' => $fuelType?->id,
                        'liters' => rand(500, 2000),
                        'working_hours' => rand(8, 20),
                        'usage_category' => FuelUsageCategory::WasteLoading,
                    ]);

                    EquipmentDeployment::create([
                        'daily_entry_id' => $entry->id,
                        'equipment_id' => $eq->equipment_id,
                        'unit_code' => $eq->unit_code,
                        'pit_id' => $eq->pit_id,
                        'shift_id' => $shifts->first()->id,
                        'prod_ob_bcm' => rand(1000, 5000),
                        'prod_coal_ton' => rand(200, 1000),
                        'operator_name' => 'Operator '.rand(1, 10),
                    ]);
                }

                SiteInfo::create([
                    'daily_entry_id' => $entry->id,
                    'weather' => ['Cerah', 'Berawan', 'Hujan Ringan'][rand(0, 2)],
                    'rain_hours' => rand(0, 4),
                    'slippery_hours' => rand(0, 2),
                    'manpower_plan' => 120,
                    'manpower_actual' => rand(100, 130),
                    'safety_notes' => 'Tidak ada insiden',
                    'fuel_stock_liters' => rand(50000, 100000),
                ]);
            }
        }
    }
}
