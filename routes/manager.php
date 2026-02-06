<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RecurringInvoiceController;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Manager Routes
|--------------------------------------------------------------------------
|
| These routes are for manager level users and above.
| They handle business operations like creating invoices, managing clients,
| and accessing business reports.
|
*/

Route::middleware(['auth', 'verified', 'organization'])->group(function () {

    // Invoice Management (Manager+ level)
    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    });

    Route::middleware('permission:invoices.create')->group(function () {
        Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::post('invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
    });

    Route::middleware('permission:invoices.update')->group(function () {
        Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    });

    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    });

    Route::middleware('permission:invoices.send')->group(function () {
        Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    });

    Route::middleware('permission:invoices.delete')->group(function () {
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    });

    // Client Management (Manager+ level)
    Route::middleware('permission:clients.view')->group(function () {
        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    });

    Route::middleware('permission:clients.create')->group(function () {
        Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    });

    Route::middleware('permission:clients.update')->group(function () {
        Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    });

    Route::middleware('permission:clients.delete')->group(function () {
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    });

    // Recurring Invoice Management (Manager+ level)
    Route::middleware('permission:recurring-invoices.view')->group(function () {
        Route::get('recurring-invoices', [RecurringInvoiceController::class, 'index'])->name('recurring-invoices.index');
        Route::get('recurring-invoices/{recurring_invoice}', [RecurringInvoiceController::class, 'show'])->name('recurring-invoices.show');
    });

    Route::middleware('permission:recurring-invoices.create')->group(function () {
        Route::get('recurring-invoices/create', [RecurringInvoiceController::class, 'create'])->name('recurring-invoices.create');
        Route::post('recurring-invoices', [RecurringInvoiceController::class, 'store'])->name('recurring-invoices.store');
    });

    Route::middleware('permission:recurring-invoices.update')->group(function () {
        Route::get('recurring-invoices/{recurring_invoice}/edit', [RecurringInvoiceController::class, 'edit'])->name('recurring-invoices.edit');
        Route::put('recurring-invoices/{recurring_invoice}', [RecurringInvoiceController::class, 'update'])->name('recurring-invoices.update');
        Route::post('recurring-invoices/{recurringInvoice}/activate', [RecurringInvoiceController::class, 'activate'])->name('recurring-invoices.activate');
        Route::post('recurring-invoices/{recurringInvoice}/deactivate', [RecurringInvoiceController::class, 'deactivate'])->name('recurring-invoices.deactivate');
        Route::post('recurring-invoices/{recurringInvoice}/generate', [RecurringInvoiceController::class, 'generateInvoice'])->name('recurring-invoices.generate');
    });

    Route::middleware('permission:recurring-invoices.delete')->group(function () {
        Route::delete('recurring-invoices/{recurring_invoice}', [RecurringInvoiceController::class, 'destroy'])->name('recurring-invoices.destroy');
    });

    // Business Reports (Manager+ level)
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/revenue', [ReportsController::class, 'revenue'])->name('revenue');
        Route::get('/clients', [ReportsController::class, 'clients'])->name('clients');
        Route::post('/export-pdf', [ReportsController::class, 'exportPdf'])->name('export-pdf');
    });
});