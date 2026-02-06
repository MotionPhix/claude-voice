<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Accountant Routes
|--------------------------------------------------------------------------
|
| These routes are for accountant level users and above.
| They handle financial operations like payment processing, payment reports,
| and financial analysis.
|
*/

Route::middleware(['auth', 'verified', 'organization'])->group(function () {

    // Payment Management (Accountant+ level)
    Route::middleware('permission:payments.view')->group(function () {
        // Payment viewing is handled through invoice views
    });

    Route::middleware('permission:payments.create')->group(function () {
        Route::get('invoices/{invoice}/payments/create', [PaymentController::class, 'create'])->name('invoices.payments.create');
        Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
    });

    Route::middleware('permission:payments.delete')->group(function () {
        Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });

    // Financial Reports (Accountant+ level)
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        Route::get('/outstanding', [ReportsController::class, 'outstanding'])->name('outstanding');
        Route::get('/payments', [ReportsController::class, 'payments'])->name('payments');
    });
});