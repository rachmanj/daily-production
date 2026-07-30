<?php

use App\Enums\MaterialType;
use App\Models\EquipmentAssignment;
use App\Models\Site;
use App\Models\User;
use App\Services\HourlyProductionService;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->user = User::where('email', 'admin@mineops.test')->first();
    $this->actingAs($this->user);
    $this->withSession(['active_site_id' => Site::where('code', '021C')->value('id')]);
});

test('admin can update equipment assignment ccr classification', function () {
    $assignment = EquipmentAssignment::query()
        ->whereHas('site', fn ($q) => $q->where('code', '021C'))
        ->where('unit_code', 'E 084')
        ->firstOrFail();

    $response = $this->put(route('equipment-assignments.update', $assignment), [
        'material_type' => MaterialType::Limestone->value,
        'equipment_role' => 'loader',
        'display_order' => 1,
        'is_active_for_tracking' => true,
    ]);

    $response->assertRedirect(route('equipment-assignments.index'));

    $assignment->refresh();

    expect($assignment->material_type)->toBe(MaterialType::Limestone)
        ->and($assignment->equipment_role)->toBe('loader')
        ->and($assignment->display_order)->toBe(1)
        ->and($assignment->is_active_for_tracking)->toBeTrue();
});

test('admin can clear material type on equipment assignment', function () {
    $assignment = EquipmentAssignment::query()
        ->whereHas('site', fn ($q) => $q->where('code', '021C'))
        ->where('unit_code', 'E 084')
        ->firstOrFail();

    $assignment->update(['material_type' => MaterialType::Limestone]);

    $response = $this->put(route('equipment-assignments.update', $assignment), [
        'material_type' => null,
        'equipment_role' => null,
        'display_order' => null,
        'is_active_for_tracking' => false,
    ]);

    $response->assertRedirect(route('equipment-assignments.index'));

    $assignment->refresh();

    expect($assignment->material_type)->toBeNull()
        ->and($assignment->equipment_role)->toBeNull()
        ->and($assignment->display_order)->toBeNull()
        ->and($assignment->is_active_for_tracking)->toBeFalse();
});

test('classified equipment appears in hourly grid', function () {
    $site = Site::where('code', '021C')->firstOrFail();

    $assignment = EquipmentAssignment::query()
        ->where('site_id', $site->id)
        ->where('unit_code', 'E 078')
        ->firstOrFail();

    $assignment->update([
        'material_type' => MaterialType::Shalestone,
        'equipment_role' => 'loader',
        'display_order' => 1,
        'is_active_for_tracking' => true,
    ]);

    $grid = app(HourlyProductionService::class)->getEquipmentGrid(
        $site->id,
        MaterialType::Shalestone,
    );

    expect(collect($grid)->pluck('unit_code')->all())->toContain('E 078');
});
