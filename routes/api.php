<?php

use App\Http\Controllers\SystemNotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
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
