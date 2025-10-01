<?php

use App\Http\Controllers\Settings\CurrencyController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');

    // Currency management routes
    Route::get('settings/currencies', [CurrencyController::class, 'index'])->name('settings.currencies');
    Route::post('settings/currencies', [CurrencyController::class, 'store'])->name('settings.currencies.store');
    Route::put('settings/currencies/{currency}', [CurrencyController::class, 'update'])->name('settings.currencies.update');
    Route::patch('settings/currencies/{currency}/toggle-status', [CurrencyController::class, 'toggleStatus'])->name('settings.currencies.toggle-status');
    Route::patch('settings/currencies/{currency}/set-base', [CurrencyController::class, 'setBase'])->name('settings.currencies.set-base');
    Route::delete('settings/currencies/{currency}', [CurrencyController::class, 'destroy'])->name('settings.currencies.destroy');
    Route::patch('settings/currencies/update-rates', [CurrencyController::class, 'updateExchangeRates'])->name('settings.currencies.update-rates');
    Route::patch('settings/currencies/bulk-toggle-status', [CurrencyController::class, 'bulkToggleStatus'])->name('settings.currencies.bulk-toggle-status');
});
