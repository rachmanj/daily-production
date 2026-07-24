<?php

namespace App\Http\Controllers;

use App\Enums\EntryStatus;
use App\Enums\FuelUsageCategory;
use App\Enums\ProductionActivity;
use App\Http\Requests\StoreDailyEntryRequest;
use App\Models\DailyEntry;
use App\Models\EquipmentAssignment;
use App\Models\FuelType;
use App\Models\Pit;
use App\Models\Shift;
use App\Models\Site;
use App\Services\DailyEntryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DailyEntryController extends Controller
{
    public function __construct(
        protected DailyEntryService $dailyEntryService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', DailyEntry::class);

        $query = DailyEntry::query()
            ->with(['site:id,code,name', 'creator:id,name'])
            ->orderByDesc('production_date');

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->integer('site_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('production_date', '>=', $request->string('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('production_date', '<=', $request->string('date_to'));
        }

        return Inertia::render('daily-entries/Index', [
            'entries' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['site_id', 'status', 'date_from', 'date_to']),
            'sites' => Site::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'statuses' => EntryStatus::options(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', DailyEntry::class);

        return Inertia::render('daily-entries/Create', [
            'sites' => Site::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
        ]);
    }

    public function store(StoreDailyEntryRequest $request): RedirectResponse
    {
        $entry = $this->dailyEntryService->create($request->validated(), $request->user()->id);

        return redirect()->route('daily-entries.edit', $entry)
            ->with('success', 'Entry harian berhasil dibuat.');
    }

    public function show(DailyEntry $dailyEntry): Response
    {
        $this->authorize('view', $dailyEntry);

        return Inertia::render('daily-entries/Show', $this->entryPayload($dailyEntry));
    }

    public function edit(DailyEntry $dailyEntry): Response
    {
        $this->authorize('update', $dailyEntry);

        return Inertia::render('daily-entries/Edit', $this->entryPayload($dailyEntry));
    }

    public function update(Request $request, DailyEntry $dailyEntry): RedirectResponse
    {
        $this->authorize('update', $dailyEntry);

        $dailyEntry->update($request->only(['production_date']));

        return redirect()->back()->with('success', 'Entry berhasil diperbarui.');
    }

    public function destroy(DailyEntry $dailyEntry): RedirectResponse
    {
        $this->authorize('delete', $dailyEntry);
        $dailyEntry->delete();

        return redirect()->route('daily-entries.index')->with('success', 'Entry berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function entryPayload(DailyEntry $dailyEntry): array
    {
        $dailyEntry->load([
            'site',
            'creator',
            'approver',
            'productionRecords.pit',
            'productionRecords.shift',
            'fuelRecords.shift',
            'fuelRecords.fuelType',
            'equipmentDeployments.pit',
            'equipmentDeployments.shift',
            'siteInfo',
        ]);

        $siteId = $dailyEntry->site_id;

        return [
            'entry' => $dailyEntry,
            'pits' => Pit::query()->where('site_id', $siteId)->where('is_active', true)->orderBy('code')->get(),
            'shifts' => Shift::query()->where('site_id', $siteId)->where('is_active', true)->orderBy('name')->get(),
            'fuelTypes' => FuelType::query()->where('is_active', true)->orderBy('name')->get(),
            'equipmentAssignments' => EquipmentAssignment::query()
                ->where('site_id', $siteId)
                ->where('is_active_for_tracking', true)
                ->orderBy('unit_code')
                ->get(),
            'productionActivities' => ProductionActivity::options(),
            'fuelCategories' => FuelUsageCategory::options(),
            'statuses' => EntryStatus::options(),
        ];
    }
}
