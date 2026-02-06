<?php

use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\SystemNotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    // Global Search
    Route::get('/search', [SearchController::class, 'search'])->name('search');
    // Invoice API
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/stats', [InvoiceController::class, 'stats'])->name('stats');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
        Route::post('/{invoice}/send', [InvoiceController::class, 'send'])->name('send');
        Route::post('/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('mark-paid');
        Route::post('/{invoice}/convert-currency', [InvoiceController::class, 'convertCurrency'])->name('convert-currency');
    });

    // System Notifications API
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [SystemNotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [SystemNotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{notification}/read', [SystemNotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/{notification}/dismiss', [SystemNotificationController::class, 'dismiss'])->name('dismiss');
        Route::post('/mark-all-read', [SystemNotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('/type/{type}', [SystemNotificationController::class, 'byType'])->name('by-type');
        Route::delete('/{notification}', [SystemNotificationController::class, 'destroy'])->name('destroy');
    });
});
