<?php

namespace App\Http\Controllers;

use App\Enums\EntryStatus;
use App\Enums\MaterialType;
use App\Http\Requests\StoreHourlyEntryRequest;
use App\Http\Requests\UpdateHourlyRecordsRequest;
use App\Models\DailyEntry;
use App\Models\Shift;
use App\Models\Site;
use App\Services\CalculationService;
use App\Services\HourlyProductionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HourlyEntryController extends Controller
{
    private const NON_DRAFT_MESSAGE = 'Hourly entry hanya dapat diedit saat Daily Entry masih berstatus Draft.';

    public function __construct(
        protected HourlyProductionService $hourlyProductionService,
        protected CalculationService $calculationService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', DailyEntry::class);

        $ccrSiteIds = Site::query()
            ->whereIn('code', config('mineops.ccr_site_codes'))
            ->pluck('id');

        $query = DailyEntry::query()
            ->with(['site:id,code,name', 'creator:id,name'])
            ->whereIn('site_id', $ccrSiteIds)
            ->orderByDesc('production_date');

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->integer('site_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('material_type')) {
            $query->whereHas('hourlyProductionRecords', fn ($q) => $q
                ->where('material_type', $request->string('material_type')));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('production_date', '>=', $request->string('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('production_date', '<=', $request->string('date_to'));
        }

        return Inertia::render('hourly/Index', [
            'entries' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['site_id', 'status', 'material_type', 'date_from', 'date_to']),
            'sites' => Site::query()->whereIn('code', config('mineops.ccr_site_codes'))->orderBy('code')->get(['id', 'code', 'name']),
            'statuses' => EntryStatus::options(),
            'materials' => MaterialType::options(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', DailyEntry::class);

        return Inertia::render('hourly/Create', [
            'sites' => Site::query()->whereIn('code', config('mineops.ccr_site_codes'))->orderBy('code')->get(['id', 'code', 'name']),
            'shifts' => Shift::query()->orderBy('id')->get(['id', 'name', 'start_time', 'end_time']),
            'materials' => MaterialType::options(),
        ]);
    }

    public function store(StoreHourlyEntryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $entry = DailyEntry::query()
            ->where('site_id', $data['site_id'])
            ->whereDate('production_date', $data['production_date'])
            ->first();

        if (! $entry) {
            $entry = $this->hourlyProductionService->createEntry($data, $request->user()->id);
        } elseif ($entry->status !== EntryStatus::Draft) {
            return redirect()
                ->route('hourly.index')
                ->with('error', self::NON_DRAFT_MESSAGE);
        }

        return redirect()->route('hourly.edit', [
            'dailyEntry' => $entry->id,
            'material_type' => $data['material_type'],
            'shift_id' => $data['shift_id'],
        ])->with('success', 'Entry CCR berhasil dibuat.');
    }

    public function edit(Request $request, DailyEntry $dailyEntry): Response|RedirectResponse
    {
        if ($redirect = $this->redirectIfNotDraft($dailyEntry, $request)) {
            return $redirect;
        }

        $this->authorize('update', $dailyEntry);

        $materialType = MaterialType::from($request->string('material_type', MaterialType::Limestone->value));
        $shiftId = $request->integer('shift_id', Shift::query()->orderBy('id')->value('id'));

        $dailyEntry->load(['site', 'creator', 'approver']);

        $equipment = $this->hourlyProductionService->getEquipmentGrid($dailyEntry->site_id, $materialType);
        $records = $this->hourlyProductionService->getRecordsForEntry($dailyEntry, $materialType);
        $date = Carbon::parse($dailyEntry->production_date);

        return Inertia::render('hourly/Edit', [
            'entry' => $dailyEntry,
            'materialType' => $materialType->value,
            'materialLabel' => $materialType->label(),
            'shiftId' => $shiftId,
            'shifts' => Shift::query()->orderBy('id')->get(['id', 'name', 'start_time', 'end_time']),
            'equipment' => $equipment,
            'records' => $records,
            'hourlyTarget' => $this->calculationService->hourlyTarget($dailyEntry->site_id, $date, $materialType),
            'materials' => MaterialType::options(),
        ]);
    }

    public function update(UpdateHourlyRecordsRequest $request, DailyEntry $dailyEntry): RedirectResponse|JsonResponse
    {
        if ($redirect = $this->redirectIfNotDraft($dailyEntry, $request)) {
            return $redirect;
        }

        $this->authorize('update', $dailyEntry);

        $data = $request->validated();
        $material = MaterialType::from($data['material_type']);

        $this->hourlyProductionService->upsertHourlyRecords(
            $dailyEntry,
            $material,
            (int) $data['shift_id'],
            $data['records'],
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data hourly berhasil disimpan.']);
        }

        return redirect()->back()->with('success', 'Data hourly berhasil disimpan.');
    }

    private function redirectIfNotDraft(DailyEntry $dailyEntry, Request $request): RedirectResponse|JsonResponse|null
    {
        if ($dailyEntry->status === EntryStatus::Draft) {
            return null;
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => self::NON_DRAFT_MESSAGE], 403);
        }

        return redirect()
            ->route('hourly.index')
            ->with('error', self::NON_DRAFT_MESSAGE);
    }
}
