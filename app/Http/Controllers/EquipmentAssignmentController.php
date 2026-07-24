<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignEquipmentRequest;
use App\Models\EquipmentAssignment;
use App\Models\Pit;
use App\Services\EquipmentApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EquipmentAssignmentController extends Controller
{
    public function __construct(
        protected EquipmentApiService $equipmentApi
    ) {}

    public function index(Request $request): Response
    {
        $activeSiteId = $request->session()->get('active_site_id');

        $assignments = EquipmentAssignment::query()
            ->with('pit:id,code')
            ->when($activeSiteId, fn ($q) => $q->where('site_id', $activeSiteId))
            ->orderBy('unit_code')
            ->get()
            ->map(fn (EquipmentAssignment $a) => [
                'id' => $a->id,
                'equipment_id' => $a->equipment_id,
                'unit_code' => $a->unit_code,
                'description' => $a->description,
                'plant_type_name' => $a->plant_type_name,
                'project_code' => $a->project_code,
                'pit_code' => $a->pit?->code,
                'pit_id' => $a->pit_id,
                'is_active_for_tracking' => $a->is_active_for_tracking,
            ]);

        $pits = Pit::query()
            ->when($activeSiteId, fn ($q) => $q->where('site_id', $activeSiteId))
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'site_id']);

        return Inertia::render('equipment-assignments/Index', [
            'assignments' => $assignments,
            'pits' => $pits,
            'plantTypes' => ['Digger', 'Hauler', 'Support', 'Heavy Equip'],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $filters = $request->only(['project_code', 'plant_type', 'search', 'is_active']);
        $filters['is_active'] = $filters['is_active'] ?? 1;

        $results = $this->equipmentApi->search($filters);

        $assignedIds = EquipmentAssignment::query()
            ->pluck('equipment_id')
            ->toArray();

        $results = array_values(array_filter($results, function ($item) use ($assignedIds) {
            return ! in_array($item['id'] ?? null, $assignedIds, true);
        }));

        return response()->json(['data' => $results]);
    }

    public function store(AssignEquipmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        foreach ($validated['equipment'] as $item) {
            EquipmentAssignment::updateOrCreate(
                [
                    'equipment_id' => $item['equipment_id'],
                    'site_id' => $validated['site_id'],
                ],
                [
                    'unit_code' => $item['unit_code'],
                    'description' => $item['description'] ?? null,
                    'plant_type_name' => $item['plant_type_name'] ?? null,
                    'project_code' => $item['project_code'],
                    'pit_id' => $validated['pit_id'],
                    'is_active_for_tracking' => true,
                    'synced_at' => now(),
                ]
            );
        }

        return redirect()->route('equipment-assignments.index')
            ->with('success', 'Equipment berhasil di-assign ke PIT.');
    }

    public function destroy(EquipmentAssignment $equipmentAssignment): RedirectResponse
    {
        $equipmentAssignment->delete();

        return redirect()->route('equipment-assignments.index')
            ->with('success', 'Assignment equipment berhasil dihapus.');
    }
}
