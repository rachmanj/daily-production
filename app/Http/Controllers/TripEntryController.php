<?php

namespace App\Http\Controllers;

use App\Enums\MaterialType;
use App\Http\Requests\StoreTripEntryRequest;
use App\Models\DailyEntry;
use App\Models\EquipmentAssignment;
use App\Models\Shift;
use App\Models\Site;
use App\Services\HourlyProductionService;
use App\Services\TripProductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TripEntryController extends Controller
{
    public function __construct(
        protected HourlyProductionService $hourlyProductionService,
        protected TripProductionService $tripProductionService,
    ) {}

    public function create(): Response
    {
        $this->authorize('create', DailyEntry::class);

        $site = Site::query()->where('code', '022C')->firstOrFail();

        return Inertia::render('ccr-022c/TripEntry', [
            'site' => $site->only(['id', 'code', 'name']),
            'shifts' => Shift::query()->orderBy('id')->get(['id', 'name']),
            'materials' => [
                MaterialType::Overburden->value => MaterialType::Overburden->label(),
                MaterialType::Coal->value => MaterialType::Coal->label(),
                MaterialType::TopSoil->value => MaterialType::TopSoil->label(),
            ],
            'excavators' => $this->equipmentOptions($site->id, 'excavator'),
            'haulers' => $this->equipmentOptions($site->id, 'hauler'),
        ]);
    }

    public function store(StoreTripEntryRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        $entry = DailyEntry::query()
            ->where('site_id', $data['site_id'])
            ->whereDate('production_date', $data['production_date'])
            ->first();

        if (! $entry) {
            $entry = $this->hourlyProductionService->createEntry($data, $request->user()->id);
        }

        $this->authorize('update', $entry);

        $excavator = EquipmentAssignment::query()
            ->where('site_id', $data['site_id'])
            ->where('equipment_id', $data['excavator_id'])
            ->first();

        $hauler = EquipmentAssignment::query()
            ->where('site_id', $data['site_id'])
            ->where('equipment_id', $data['hauler_id'])
            ->first();

        $this->tripProductionService->upsertTrips($entry, [[
            'excavator_id' => $data['excavator_id'],
            'excavator_code' => $excavator?->unit_code,
            'hauler_id' => $data['hauler_id'],
            'hauler_code' => $hauler?->unit_code,
            'shift_id' => $data['shift_id'],
            'material_type' => $data['material_type'],
            'hour_slot' => $data['hour_slot'],
            'volume_bcm' => $data['volume_bcm'],
            'load_percent' => $data['load_percent'] ?? 100,
            'trip_count' => $data['trip_count'] ?? 1,
            'truck_capacity_bcm' => $data['truck_capacity_bcm'] ?? 0,
        ]]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'daily_entry_id' => $entry->id]);
        }

        return redirect()->back()->with('success', 'Trip berhasil disimpan.');
    }

    /**
     * @return array<int, array{equipment_id: int, unit_code: string}>
     */
    protected function equipmentOptions(int $siteId, string $role): array
    {
        return EquipmentAssignment::query()
            ->where('site_id', $siteId)
            ->where('equipment_role', $role)
            ->where('is_active_for_tracking', true)
            ->orderBy('display_order')
            ->orderBy('unit_code')
            ->get(['equipment_id', 'unit_code'])
            ->map(fn (EquipmentAssignment $a) => [
                'equipment_id' => $a->equipment_id,
                'unit_code' => $a->unit_code,
            ])
            ->all();
    }
}
