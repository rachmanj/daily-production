<?php

namespace App\Http\Controllers;

use App\Enums\ShiftName;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ShiftController extends Controller
{
    public function index(): Response
    {
        $shifts = Shift::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Shift $shift) => [
                'id' => $shift->id,
                'name' => $shift->name->value,
                'name_label' => $shift->name->label(),
                'start_time' => substr((string) $shift->start_time, 0, 5),
                'end_time' => substr((string) $shift->end_time, 0, 5),
            ]);

        return Inertia::render('shifts/Index', [
            'shifts' => $shifts,
            'nameOptions' => ShiftName::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('shifts/Create', [
            'nameOptions' => ShiftName::options(),
        ]);
    }

    public function store(StoreShiftRequest $request): RedirectResponse
    {
        Shift::create($request->validated());

        return redirect()->route('shifts.index')->with('success', 'Shift berhasil ditambahkan.');
    }

    public function edit(Shift $shift): Response
    {
        return Inertia::render('shifts/Edit', [
            'shift' => [
                'id' => $shift->id,
                'name' => $shift->name->value,
                'start_time' => substr((string) $shift->start_time, 0, 5),
                'end_time' => substr((string) $shift->end_time, 0, 5),
            ],
            'nameOptions' => ShiftName::options(),
        ]);
    }

    public function update(UpdateShiftRequest $request, Shift $shift): RedirectResponse
    {
        $shift->update($request->validated());

        return redirect()->route('shifts.index')->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $shift->delete();

        return redirect()->route('shifts.index')->with('success', 'Shift berhasil dihapus.');
    }
}
