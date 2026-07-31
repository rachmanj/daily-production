<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyEntry;
use App\Services\TripAggregationService;
use App\Services\TripProductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CcrPairingController extends Controller
{
    public function __construct(
        protected TripProductionService $tripProductionService,
        protected TripAggregationService $tripAggregationService,
    ) {}

    public function pairing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'daily_entry_id' => ['required', 'exists:daily_entries,id'],
        ]);

        $dailyEntryId = (int) $validated['daily_entry_id'];

        return response()->json([
            'pairs' => $this->tripProductionService->getPairing($dailyEntryId),
        ]);
    }

    public function reconciliation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'daily_entry_id' => ['required', 'exists:daily_entries,id'],
        ]);

        $dailyEntryId = (int) $validated['daily_entry_id'];
        $entry = DailyEntry::query()->with('site')->findOrFail($dailyEntryId);

        return response()->json([
            'reconciliation' => $this->tripAggregationService->reconcile($dailyEntryId),
            'production_source' => config("mineops.production_source.{$entry->site->code}", 'parallel'),
        ]);
    }
}
