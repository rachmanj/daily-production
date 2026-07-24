<?php

use App\Http\Controllers\EquipmentAssignmentController;
use App\Http\Controllers\FuelPriceController;
use App\Http\Controllers\FuelTypeController;
use App\Http\Controllers\PitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteSwitchController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

    Route::post('/site-switch', [SiteSwitchController::class, 'store'])->name('site-switch.store');

    Route::middleware('role:admin')->group(function () {
        Route::resource('sites', SiteController::class)->except(['show']);
        Route::resource('pits', PitController::class)->except(['show']);
        Route::resource('shifts', ShiftController::class)->except(['show']);
        Route::resource('fuel-types', FuelTypeController::class)->except(['show']);
        Route::resource('fuel-prices', FuelPriceController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('roles', RoleController::class)->only(['index', 'edit', 'update']);

        Route::get('/equipment-assignments/search', [EquipmentAssignmentController::class, 'search'])
            ->name('equipment-assignments.search');
        Route::resource('equipment-assignments', EquipmentAssignmentController::class)
            ->only(['index', 'store', 'destroy']);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
