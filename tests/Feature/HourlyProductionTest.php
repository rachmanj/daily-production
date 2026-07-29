<?php

use App\Enums\EntryStatus;
use App\Enums\MaterialType;
use App\Models\DailyEntry;
use App\Models\HourlyProductionRecord;
use App\Models\Shift;
use App\Models\Site;
use App\Models\User;
use App\Services\CalculationService;
use App\Services\HourlyProductionService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->user = User::where('email', 'admin@mineops.test')->first();
    $this->actingAs($this->user);
});

test('materialDtd sums approved hourly records for date', function () {
    $site = Site::where('code', '021C')->firstOrFail();
    $shiftId = Shift::query()->orderBy('id')->value('id');

    $entry = DailyEntry::create([
        'uuid' => (string) Str::uuid(),
        'production_date' => '2026-07-29',
        'site_id' => $site->id,
        'created_by' => $this->user->id,
        'status' => EntryStatus::Approved,
        'source' => 'manual',
    ]);

    HourlyProductionRecord::create([
        'daily_entry_id' => $entry->id,
        'equipment_id' => 2000,
        'unit_code' => 'E 084',
        'shift_id' => $shiftId,
        'material_type' => MaterialType::Limestone,
        'hour_slot' => 8,
        'tonnage' => 120,
    ]);

    HourlyProductionRecord::create([
        'daily_entry_id' => $entry->id,
        'equipment_id' => 2001,
        'unit_code' => 'E 098',
        'shift_id' => $shiftId,
        'material_type' => MaterialType::Limestone,
        'hour_slot' => 9,
        'tonnage' => 95,
    ]);

    $calc = app(CalculationService::class);
    $date = Carbon::parse('2026-07-29');

    expect($calc->materialDtd($site->id, $date, MaterialType::Limestone))->toBe(215.0);
});

test('materialMtd sums approved records for month', function () {
    $site = Site::where('code', '021C')->firstOrFail();
    $shiftId = Shift::query()->orderBy('id')->value('id');

    foreach (['2026-07-28', '2026-07-29'] as $dateStr) {
        $entry = DailyEntry::create([
            'uuid' => (string) Str::uuid(),
            'production_date' => $dateStr,
            'site_id' => $site->id,
            'created_by' => $this->user->id,
            'status' => EntryStatus::Approved,
            'source' => 'manual',
        ]);

        HourlyProductionRecord::create([
            'daily_entry_id' => $entry->id,
            'equipment_id' => 2000,
            'unit_code' => 'E 084',
            'shift_id' => $shiftId,
            'material_type' => MaterialType::Limestone,
            'hour_slot' => 8,
            'tonnage' => 100,
        ]);
    }

    $calc = app(CalculationService::class);
    expect($calc->materialMtd($site->id, Carbon::parse('2026-07-29'), MaterialType::Limestone))->toBe(200.0);
});

test('hourlyTarget derives from material daily plan', function () {
    $site = Site::where('code', '021C')->firstOrFail();
    $calc = app(CalculationService::class);
    $date = Carbon::now();

    $target = $calc->hourlyTarget($site->id, $date, MaterialType::Limestone);

    expect($target)->toBe(541.65);
});

test('hourly production service upserts records', function () {
    $site = Site::where('code', '021C')->firstOrFail();
    $shiftId = Shift::query()->orderBy('id')->value('id');
    $service = app(HourlyProductionService::class);

    $entry = $service->createEntry([
        'production_date' => '2026-07-30',
        'site_id' => $site->id,
    ], $this->user->id);

    $service->upsertHourlyRecords($entry, MaterialType::Limestone, $shiftId, [
        ['equipment_id' => 2000, 'hour_slot' => 8, 'tonnage' => 150, 'unit_code' => 'E 084'],
        ['equipment_id' => 2001, 'hour_slot' => 8, 'tonnage' => 120, 'unit_code' => 'E 098'],
    ]);

    expect(HourlyProductionRecord::where('daily_entry_id', $entry->id)->count())->toBe(2);

    $service->upsertHourlyRecords($entry, MaterialType::Limestone, $shiftId, [
        ['equipment_id' => 2000, 'hour_slot' => 8, 'tonnage' => 200, 'unit_code' => 'E 084'],
    ]);

    expect(HourlyProductionRecord::where('daily_entry_id', $entry->id)->count())->toBe(2);
    expect((float) HourlyProductionRecord::where('equipment_id', 2000)->first()->tonnage)->toBe(200.0);
});

test('hourly entry pages are accessible', function () {
    $this->get(route('hourly.index'))->assertOk();
    $this->get(route('hourly.create'))->assertOk();
    $this->get(route('hourly-dashboard.index'))->assertOk();
});

test('hourly crud via controller stores records', function () {
    $site = Site::where('code', '021C')->firstOrFail();
    $shiftId = Shift::query()->orderBy('id')->value('id');

    $this->post(route('hourly.store'), [
        'production_date' => '2026-07-31',
        'site_id' => $site->id,
        'material_type' => 'limestone',
        'shift_id' => $shiftId,
    ])->assertRedirect();

    $entry = DailyEntry::where('site_id', $site->id)
        ->whereDate('production_date', '2026-07-31')
        ->first();

    expect($entry)->not->toBeNull();

    $this->put(route('hourly.update', $entry), [
        'material_type' => 'limestone',
        'shift_id' => $shiftId,
        'records' => [
            ['equipment_id' => 2000, 'hour_slot' => 10, 'tonnage' => 88, 'unit_code' => 'E 084'],
        ],
    ])->assertRedirect();

    expect(HourlyProductionRecord::where('daily_entry_id', $entry->id)->count())->toBe(1);
});

test('equipment grid api returns ccr equipment', function () {
    $site = Site::where('code', '021C')->firstOrFail();

    $response = $this->getJson(route('hourly-data.equipmentGrid', [
        'site_id' => $site->id,
        'material' => 'limestone',
    ]));

    $response->assertOk();
    expect(count($response->json('equipment')))->toBe(5);
});
