<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFuelTypeRequest;
use App\Http\Requests\UpdateFuelTypeRequest;
use App\Models\FuelType;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FuelTypeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('fuel-types/Index', [
            'fuelTypes' => FuelType::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('fuel-types/Create');
    }

    public function store(StoreFuelTypeRequest $request): RedirectResponse
    {
        FuelType::create($request->validated());

        return redirect()->route('fuel-types.index')->with('success', 'Jenis bahan bakar berhasil ditambahkan.');
    }

    public function edit(FuelType $fuelType): Response
    {
        return Inertia::render('fuel-types/Edit', [
            'fuelType' => $fuelType,
        ]);
    }

    public function update(UpdateFuelTypeRequest $request, FuelType $fuelType): RedirectResponse
    {
        $fuelType->update($request->validated());

        return redirect()->route('fuel-types.index')->with('success', 'Jenis bahan bakar berhasil diperbarui.');
    }

    public function destroy(FuelType $fuelType): RedirectResponse
    {
        $fuelType->delete();

        return redirect()->route('fuel-types.index')->with('success', 'Jenis bahan bakar berhasil dihapus.');
    }
}
