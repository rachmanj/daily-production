<?php

use App\Http\Controllers\Ccr022cImportController;
use App\Http\Controllers\DailyEntryController;
use App\Http\Controllers\DailyEntryWorkflowController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentAssignmentController;
use App\Http\Controllers\EquipmentDeploymentController;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\FuelPriceController;
use App\Http\Controllers\FuelRecordController;
use App\Http\Controllers\FuelTypeController;
use App\Http\Controllers\HourlyDashboardController;
use App\Http\Controllers\HourlyEntryController;
use App\Http\Controllers\MonthlyPlanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PitController;
use App\Http\Controllers\PlanTargetController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\ProductionRecordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteInfoController;
use App\Http\Controllers\SiteSwitchController;
use App\Http\Controllers\TripEntryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VarianceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/fuel', [DashboardController::class, 'fuel'])->name('dashboard.fuel');

    Route::post('/site-switch', [SiteSwitchController::class, 'store'])->name('site-switch.store');

    // Daily Entries
    Route::resource('daily-entries', DailyEntryController::class);
    Route::post('/daily-entries/{dailyEntry}/submit', [DailyEntryWorkflowController::class, 'submit'])->name('daily-entries.submit');
    Route::post('/daily-entries/{dailyEntry}/approve', [DailyEntryWorkflowController::class, 'approve'])->name('daily-entries.approve');
    Route::post('/daily-entries/{dailyEntry}/reject', [DailyEntryWorkflowController::class, 'reject'])->name('daily-entries.reject');
    Route::put('/daily-entries/{dailyEntry}/production', [ProductionRecordController::class, 'update'])->name('production-records.update');
    Route::put('/daily-entries/{dailyEntry}/fuel', [FuelRecordController::class, 'update'])->name('fuel-records.update');
    Route::put('/daily-entries/{dailyEntry}/deployment', [EquipmentDeploymentController::class, 'update'])->name('equipment-deployments.update');
    Route::put('/daily-entries/{dailyEntry}/site-info', [SiteInfoController::class, 'update'])->name('site-info.update');

    // CCR Hourly
    Route::get('/hourly', [HourlyEntryController::class, 'index'])->name('hourly.index');
    Route::get('/hourly/create', [HourlyEntryController::class, 'create'])->name('hourly.create');
    Route::post('/hourly', [HourlyEntryController::class, 'store'])->name('hourly.store');
    Route::get('/hourly/{dailyEntry}/edit', [HourlyEntryController::class, 'edit'])->name('hourly.edit');
    Route::put('/hourly/{dailyEntry}', [HourlyEntryController::class, 'update'])->name('hourly.update');
    Route::get('/hourly-dashboard', [HourlyDashboardController::class, 'index'])->name('hourly-dashboard.index');
    Route::get('/hourly/report/export', [HourlyDashboardController::class, 'export'])->name('hourly.report.export');

    // CCR 022C Trip
    Route::get('/ccr-022c/import', [Ccr022cImportController::class, 'create'])->name('ccr-022c.import.create');
    Route::post('/ccr-022c/import', [Ccr022cImportController::class, 'store'])->name('ccr-022c.import.store');
    Route::get('/ccr-022c/import/{batch}/preview', [Ccr022cImportController::class, 'preview'])->name('ccr-022c.import.preview');
    Route::post('/ccr-022c/import/{batch}/confirm', [Ccr022cImportController::class, 'confirm'])->name('ccr-022c.import.confirm');
    Route::get('/ccr-022c/trip-entry', [TripEntryController::class, 'create'])->name('ccr-022c.trip-entry.create');
    Route::post('/ccr-022c/trip-entry', [TripEntryController::class, 'store'])->name('ccr-022c.trip-entry.store');

    // Excel Import
    Route::get('/excel-imports/create', [ExcelImportController::class, 'create'])->name('excel-imports.create');
    Route::post('/excel-imports', [ExcelImportController::class, 'store'])->name('excel-imports.store');
    Route::get('/excel-imports/{batch}/preview', [ExcelImportController::class, 'preview'])->name('excel-imports.preview');
    Route::post('/excel-imports/{batch}/confirm', [ExcelImportController::class, 'confirm'])->name('excel-imports.confirm');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('/reports/custom', [ReportController::class, 'custom'])->name('reports.custom');
    Route::post('/reports/custom', [ReportController::class, 'customGenerate'])->name('reports.customGenerate');
    Route::get('/reports/consolidated', [ReportController::class, 'consolidated'])->name('reports.consolidated');
    Route::post('/reports/consolidated', [ReportController::class, 'consolidatedGenerate'])->name('reports.consolidatedGenerate');
    Route::get('/reports/download/{file}', [ReportController::class, 'download'])->name('reports.download');

    // Monthly Plans & Variance
    Route::resource('monthly-plans', MonthlyPlanController::class)->except(['show']);
    Route::put('/monthly-plans/{monthlyPlan}/targets', [PlanTargetController::class, 'update'])->name('plan-targets.update');
    Route::get('/variance', [VarianceController::class, 'index'])->name('variance.index');

    // Procurement
    Route::get('/procurement', [ProcurementController::class, 'index'])->name('procurement.index');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

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
            ->only(['index', 'store', 'update', 'destroy']);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
