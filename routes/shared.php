<?php

use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Shared Routes
|--------------------------------------------------------------------------
|
| These routes are accessible to all authenticated users regardless of role.
| They include basic navigation, organization selection, and currency utilities.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Organization selection (available to all users)
    Route::prefix('organizations')->name('organizations.')->group(function () {
        Route::get('/', [OrganizationController::class, 'index'])->name('index');
        Route::get('/select', [OrganizationController::class, 'select'])->name('select');
        Route::get('/create', [OrganizationController::class, 'create'])->name('create');
        Route::post('/', [OrganizationController::class, 'store'])->name('store');
        Route::post('/{organization}/switch', [OrganizationController::class, 'switch'])->name('switch');
    });

    // Dashboard (available to all users, but content varies by role)
    Route::middleware('organization')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Currency utilities (available to all users for reference)
        Route::prefix('currencies')->name('currencies.')->group(function () {
            Route::get('/', [CurrencyController::class, 'index'])->name('index');
            Route::get('/{currency}', [CurrencyController::class, 'show'])->name('show');
            Route::post('/convert', [CurrencyController::class, 'convert'])->name('convert');

            // Currency management requires special permissions
            Route::middleware('permission:settings.update')->group(function () {
                Route::get('/create', [CurrencyController::class, 'create'])->name('create');
                Route::post('/', [CurrencyController::class, 'store'])->name('store');
                Route::get('/{currency}/edit', [CurrencyController::class, 'edit'])->name('edit');
                Route::put('/{currency}', [CurrencyController::class, 'update'])->name('update');
                Route::delete('/{currency}', [CurrencyController::class, 'destroy'])->name('destroy');
                Route::post('/update-rates', [CurrencyController::class, 'updateRates'])->name('update-rates');
                Route::post('/{currency}/set-base', [CurrencyController::class, 'setBase'])->name('set-base');
            });
        });
    });
});