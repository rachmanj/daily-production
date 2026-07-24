<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFuelPriceRequest;
use App\Http\Requests\UpdateFuelPriceRequest;
use App\Models\FuelPrice;
use App\Models\FuelType;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FuelPriceController extends Controller
{
    public function index(): Response
    {
        $fuelPrices = FuelPrice::query()
            ->with('fuelType:id,name')
            ->orderByDesc('effective_date')
            ->get()
            ->map(fn (FuelPrice $price) => [
                'id' => $price->id,
                'fuel_type_id' => $price->fuel_type_id,
                'fuel_type_name' => $price->fuelType?->name,
                'price_per_liter' => $price->price_per_liter,
                'effective_date' => $price->effective_date->format('Y-m-d'),
            ]);

        return Inertia::render('fuel-prices/Index', [
            'fuelPrices' => $fuelPrices,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('fuel-prices/Create', [
            'fuelTypes' => FuelType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreFuelPriceRequest $request): RedirectResponse
    {
        FuelPrice::create($request->validated());

        return redirect()->route('fuel-prices.index')->with('success', 'Harga bahan bakar berhasil ditambahkan.');
    }

    public function edit(FuelPrice $fuelPrice): Response
    {
        return Inertia::render('fuel-prices/Edit', [
            'fuelPrice' => [
                'id' => $fuelPrice->id,
                'fuel_type_id' => $fuelPrice->fuel_type_id,
                'price_per_liter' => $fuelPrice->price_per_liter,
                'effective_date' => $fuelPrice->effective_date->format('Y-m-d'),
            ],
            'fuelTypes' => FuelType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateFuelPriceRequest $request, FuelPrice $fuelPrice): RedirectResponse
    {
        $fuelPrice->update($request->validated());

        return redirect()->route('fuel-prices.index')->with('success', 'Harga bahan bakar berhasil diperbarui.');
    }

    public function destroy(FuelPrice $fuelPrice): RedirectResponse
    {
        $fuelPrice->delete();

        return redirect()->route('fuel-prices.index')->with('success', 'Harga bahan bakar berhasil dihapus.');
    }
}
