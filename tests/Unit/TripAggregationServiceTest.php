<?php

namespace Tests\Unit;

use App\Enums\EntryStatus;
use App\Enums\MaterialType;
use App\Models\DailyEntry;
use App\Models\HourlyProductionRecord;
use App\Models\Pit;
use App\Models\ProductionRecord;
use App\Models\Shift;
use App\Models\Site;
use App\Models\TripProductionRecord;
use App\Models\User;
use App\Services\TripAggregationService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TripAggregationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TripAggregationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->service = app(TripAggregationService::class);
    }

    public function test_rollup_trip_to_hourly_aggregates_by_excavator_material_and_hour(): void
    {
        $site = Site::where('code', '022C')->firstOrFail();
        $shiftId = Shift::query()->orderBy('id')->value('id');
        $user = User::where('email', 'admin@mineops.test')->firstOrFail();

        $entry = DailyEntry::create([
            'uuid' => (string) Str::uuid(),
            'production_date' => '2026-07-31',
            'site_id' => $site->id,
            'created_by' => $user->id,
            'status' => EntryStatus::Draft,
            'source' => 'manual',
        ]);

        TripProductionRecord::create([
            'daily_entry_id' => $entry->id,
            'excavator_id' => 4000,
            'excavator_code' => 'E 090',
            'hauler_id' => 4100,
            'hauler_code' => 'ADT 001',
            'shift_id' => $shiftId,
            'material_type' => MaterialType::Overburden,
            'hour_slot' => 8,
            'volume_bcm' => 100,
            'trip_count' => 1,
        ]);

        TripProductionRecord::create([
            'daily_entry_id' => $entry->id,
            'excavator_id' => 4000,
            'excavator_code' => 'E 090',
            'hauler_id' => 4101,
            'hauler_code' => 'ADT 002',
            'shift_id' => $shiftId,
            'material_type' => MaterialType::Overburden,
            'hour_slot' => 8,
            'volume_bcm' => 50,
            'trip_count' => 1,
        ]);

        TripProductionRecord::create([
            'daily_entry_id' => $entry->id,
            'excavator_id' => 4001,
            'excavator_code' => 'E 092',
            'hauler_id' => 4100,
            'hauler_code' => 'ADT 001',
            'shift_id' => $shiftId,
            'material_type' => MaterialType::TopSoil,
            'hour_slot' => 9,
            'volume_bcm' => 30,
            'trip_count' => 1,
        ]);

        $this->service->rollupTripToHourly($entry->id);

        $obHour8 = HourlyProductionRecord::query()
            ->where('daily_entry_id', $entry->id)
            ->where('equipment_id', 4000)
            ->where('material_type', MaterialType::Overburden)
            ->where('hour_slot', 8)
            ->first();

        $this->assertNotNull($obHour8);
        $this->assertEquals(150.0, (float) $obHour8->tonnage);

        $tsHour9 = HourlyProductionRecord::query()
            ->where('daily_entry_id', $entry->id)
            ->where('equipment_id', 4001)
            ->where('material_type', MaterialType::TopSoil)
            ->where('hour_slot', 9)
            ->first();

        $this->assertNotNull($tsHour9);
        $this->assertEquals(30.0, (float) $tsHour9->tonnage);
    }

    public function test_rollup_trip_to_daily_aggregates_ob_coal_and_truck_count(): void
    {
        $site = Site::where('code', '022C')->firstOrFail();
        $shiftId = Shift::query()->orderBy('id')->value('id');
        $user = User::where('email', 'admin@mineops.test')->firstOrFail();
        $pitId = Pit::where('site_id', $site->id)->value('id');

        $this->assertNotNull($pitId);

        $entry = DailyEntry::create([
            'uuid' => (string) Str::uuid(),
            'production_date' => '2026-07-31',
            'site_id' => $site->id,
            'created_by' => $user->id,
            'status' => EntryStatus::Draft,
            'source' => 'manual',
        ]);

        TripProductionRecord::create([
            'daily_entry_id' => $entry->id,
            'excavator_id' => 4000,
            'excavator_code' => 'E 090',
            'hauler_id' => 4100,
            'hauler_code' => 'ADT 001',
            'shift_id' => $shiftId,
            'material_type' => MaterialType::Overburden,
            'hour_slot' => 8,
            'volume_bcm' => 200,
            'trip_count' => 1,
        ]);

        TripProductionRecord::create([
            'daily_entry_id' => $entry->id,
            'excavator_id' => 4000,
            'excavator_code' => 'E 090',
            'hauler_id' => 4101,
            'hauler_code' => 'ADT 002',
            'shift_id' => $shiftId,
            'material_type' => MaterialType::Coal,
            'hour_slot' => 10,
            'volume_bcm' => 80,
            'trip_count' => 1,
        ]);

        $this->service->rollupTripToDaily($entry->id);

        $production = ProductionRecord::query()
            ->where('daily_entry_id', $entry->id)
            ->where('shift_id', $shiftId)
            ->first();

        $this->assertNotNull($production);
        $this->assertEquals(200.0, (float) $production->ob_removal_bcm);
        $this->assertEquals(80.0, (float) $production->coal_getting_ton);
        $this->assertEquals(2, $production->truck_count);
    }

    public function test_rollup_is_idempotent(): void
    {
        $site = Site::where('code', '022C')->firstOrFail();
        $shiftId = Shift::query()->orderBy('id')->value('id');
        $user = User::where('email', 'admin@mineops.test')->firstOrFail();

        $entry = DailyEntry::create([
            'uuid' => (string) Str::uuid(),
            'production_date' => '2026-07-31',
            'site_id' => $site->id,
            'created_by' => $user->id,
            'status' => EntryStatus::Draft,
            'source' => 'manual',
        ]);

        TripProductionRecord::create([
            'daily_entry_id' => $entry->id,
            'excavator_id' => 4000,
            'excavator_code' => 'E 090',
            'hauler_id' => 4100,
            'hauler_code' => 'ADT 001',
            'shift_id' => $shiftId,
            'material_type' => MaterialType::Overburden,
            'hour_slot' => 8,
            'volume_bcm' => 75,
            'trip_count' => 1,
        ]);

        $this->service->rollupTripToHourly($entry->id);
        $this->service->rollupTripToHourly($entry->id);

        $count = HourlyProductionRecord::query()
            ->where('daily_entry_id', $entry->id)
            ->count();

        $this->assertEquals(1, $count);
    }
}
