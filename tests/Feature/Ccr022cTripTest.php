<?php

use App\Enums\MaterialType;
use App\Models\DailyEntry;
use App\Models\Shift;
use App\Models\Site;
use App\Models\User;
use App\Services\TripProductionService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->user = User::where('email', 'admin@mineops.test')->first();
    $this->actingAs($this->user);
});

test('ccr pairing endpoint returns excavator hauler aggregates', function () {
    $site = Site::where('code', '022C')->firstOrFail();
    $shiftId = Shift::query()->orderBy('id')->value('id');

    $entry = DailyEntry::create([
        'uuid' => (string) Str::uuid(),
        'production_date' => '2026-07-31',
        'site_id' => $site->id,
        'created_by' => $this->user->id,
        'status' => 'draft',
        'source' => 'manual',
    ]);

    app(TripProductionService::class)->upsertTrips($entry, [
        [
            'excavator_id' => 4000,
            'excavator_code' => 'E 090',
            'hauler_id' => 4100,
            'hauler_code' => 'ADT 001',
            'shift_id' => $shiftId,
            'material_type' => 'ob',
            'hour_slot' => 8,
            'volume_bcm' => 50,
            'load_percent' => 95,
            'trip_count' => 1,
        ],
        [
            'excavator_id' => 4000,
            'excavator_code' => 'E 090',
            'hauler_id' => 4101,
            'hauler_code' => 'ADT 002',
            'shift_id' => $shiftId,
            'material_type' => 'ob',
            'hour_slot' => 9,
            'volume_bcm' => 60,
            'load_percent' => 100,
            'trip_count' => 2,
        ],
    ]);

    $response = $this->getJson("/api/ccr/pairing?daily_entry_id={$entry->id}");

    $response->assertOk();
    $pairs = $response->json('pairs');
    expect($pairs)->toHaveCount(1);
    expect($pairs[0]['excavator_code'])->toBe('E 090');
    expect($pairs[0]['haulers'])->toHaveCount(2);
});

test('ccr 022c import pages are accessible', function () {
    $this->get(route('ccr-022c.import.create'))->assertOk();
    $this->get(route('ccr-022c.trip-entry.create'))->assertOk();
});

test('top soil material type is available', function () {
    expect(MaterialType::TopSoil->value)->toBe('top_soil');
    expect(MaterialType::TopSoil->label())->toBe('Top Soil');
});
