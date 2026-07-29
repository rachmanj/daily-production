<?php

use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\DashboardDataController;
use App\Http\Controllers\HourlyDashboardController;
use App\Http\Controllers\HourlyDataController;
use App\Http\Controllers\ProcurementDataController;
use App\Http\Controllers\VarianceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('dashboard')->name('dashboard-data.')->group(function () {
        Route::get('/kpi', [DashboardDataController::class, 'kpi'])->name('kpi');
        Route::get('/trend', [DashboardDataController::class, 'trend'])->name('trend');
        Route::get('/utilization', [DashboardDataController::class, 'utilization'])->name('utilization');
        Route::get('/per-pit', [DashboardDataController::class, 'perPit'])->name('perPit');
        Route::get('/drilldown', [DashboardDataController::class, 'drilldown'])->name('drilldown');
        Route::get('/fuel-by-equipment', [DashboardDataController::class, 'fuelByEquipment'])->name('fuelByEquipment');
        Route::get('/consolidated', [DashboardDataController::class, 'consolidated'])->name('consolidated');
    });

    Route::prefix('procurement')->name('procurement-data.')->group(function () {
        Route::get('/po-sent', [ProcurementDataController::class, 'poSent'])->name('poSent');
        Route::get('/grpo', [ProcurementDataController::class, 'grpo'])->name('grpo');
        Route::get('/npi', [ProcurementDataController::class, 'npi'])->name('npi');
        Route::get('/budget', [ProcurementDataController::class, 'budget'])->name('budget');
        Route::get('/all-projects', [ProcurementDataController::class, 'allProjects'])->name('allProjects');
    });

    Route::prefix('sync')->name('sync.')->group(function () {
        Route::post('/daily-entries', [SyncController::class, 'dailyEntries'])->name('dailyEntries');
        Route::get('/status', [SyncController::class, 'status'])->name('status');
    });

    Route::prefix('hourly')->name('hourly-data.')->group(function () {
        Route::get('/equipment-grid', [HourlyDataController::class, 'equipmentGrid'])->name('equipmentGrid');
    });

    Route::prefix('hourly-dashboard')->name('hourly-dashboard-data.')->group(function () {
        Route::get('/kpi', [HourlyDashboardController::class, 'kpi'])->name('kpi');
        Route::get('/heatmap', [HourlyDashboardController::class, 'heatmap'])->name('heatmap');
        Route::get('/fleet', [HourlyDashboardController::class, 'fleet'])->name('fleet');
        Route::get('/trend', [HourlyDashboardController::class, 'trend'])->name('trend');
    });

    Route::get('/variance/data', [VarianceController::class, 'data'])->name('variance.data');
});
