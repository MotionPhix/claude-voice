<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RecurringInvoiceController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', []);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Invoice routes
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');

    // Client routes
    Route::resource('clients', ClientController::class);

    // Payment routes
    Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // Recurring Invoice routes
    Route::resource('recurring-invoices', RecurringInvoiceController::class);
    Route::post('recurring-invoices/{recurringInvoice}/activate', [RecurringInvoiceController::class, 'activate'])->name('recurring-invoices.activate');
    Route::post('recurring-invoices/{recurringInvoice}/deactivate', [RecurringInvoiceController::class, 'deactivate'])->name('recurring-invoices.deactivate');
    Route::post('recurring-invoices/{recurringInvoice}/generate', [RecurringInvoiceController::class, 'generateInvoice'])->name('recurring-invoices.generate');

    // Currency routes
    Route::resource('currencies', CurrencyController::class);
    Route::post('currencies/update-rates', [CurrencyController::class, 'updateRates'])->name('currencies.update-rates');
    Route::post('currencies/{currency}/set-base', [CurrencyController::class, 'setBase'])->name('currencies.set-base');
    Route::post('currencies/convert', [CurrencyController::class, 'convert'])->name('currencies.convert');

    // Reports routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/revenue', [ReportsController::class, 'revenue'])->name('revenue');
        Route::get('/outstanding', [ReportsController::class, 'outstanding'])->name('outstanding');
        Route::get('/payments', [ReportsController::class, 'payments'])->name('payments');
        Route::get('/clients', [ReportsController::class, 'clients'])->name('clients');
        Route::post('/export-pdf', [ReportsController::class, 'exportPdf'])->name('export-pdf');
    });

    // Settings routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');

        Route::match(['GET', 'POST'], '/company', [SettingsController::class, 'company'])->name('company');
        Route::match(['GET', 'POST'], '/invoice', [SettingsController::class, 'invoice'])->name('invoice');
        Route::match(['GET', 'POST'], '/email', [SettingsController::class, 'email'])->name('email');
        Route::match(['GET', 'POST'], '/payment', [SettingsController::class, 'payment'])->name('payment');

        Route::post('/test-email', [SettingsController::class, 'testEmail'])->name('test-email');
        Route::post('/backup', [SettingsController::class, 'backup'])->name('backup');
        Route::post('/clear-cache', [SettingsController::class, 'clearCache'])->name('clear-cache');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
