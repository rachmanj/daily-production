<?php

namespace App\Http\Controllers;

use App\Enums\MaterialType;
use App\Services\HourlyProductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HourlyDataController extends Controller
{
    public function __construct(
        protected HourlyProductionService $hourlyProductionService,
    ) {}

    public function equipmentGrid(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'site_id' => ['required', 'exists:sites,id'],
            'material' => ['required', 'string'],
        ]);

        $material = MaterialType::from($validated['material']);

        return response()->json([
            'equipment' => $this->hourlyProductionService->getEquipmentGrid(
                (int) $validated['site_id'],
                $material,
            ),
        ]);
    }
}
