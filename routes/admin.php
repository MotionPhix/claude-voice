<?php

use App\Http\Controllers\InvoiceTemplateController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are specifically for admin and owner level users.
| They handle organization management, member management, and system settings.
|
*/

Route::middleware(['auth', 'verified', 'organization'])->group(function () {

    // Organization Management (Admin/Owner only)
    Route::prefix('organizations')->name('organizations.')->group(function () {
        Route::get('/{organization}/settings', [OrganizationController::class, 'settings'])
            ->name('settings')
            ->middleware('permission:organization.update');

        Route::put('/{organization}', [OrganizationController::class, 'update'])
            ->name('update')
            ->middleware('permission:organization.update');

        Route::delete('/{organization}', [OrganizationController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:organization.delete');

        // Member management (Admin/Owner only)
        Route::prefix('{organization}/members')->name('members.')->group(function () {
            Route::get('/invite', [MemberController::class, 'invite'])
                ->name('invite')
                ->middleware('permission:members.invite');

            Route::post('/invite', [MemberController::class, 'sendInvite'])
                ->name('send-invite')
                ->middleware('permission:members.invite');

            Route::put('/{member}/role', [MemberController::class, 'updateRole'])
                ->name('update-role')
                ->middleware('permission:members.manage');

            Route::delete('/{member}', [MemberController::class, 'remove'])
                ->name('remove')
                ->middleware('permission:members.remove');
        });
    });

    // Invoice Templates (Admin/Owner level)
    Route::prefix('invoice-templates')->name('invoice-templates.')->middleware('permission:settings.update')->group(function () {
        Route::get('/', [InvoiceTemplateController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceTemplateController::class, 'create'])->name('create');
        Route::post('/', [InvoiceTemplateController::class, 'store'])->name('store');
        Route::get('/{invoiceTemplate}/edit', [InvoiceTemplateController::class, 'edit'])->name('edit');
        Route::put('/{invoiceTemplate}', [InvoiceTemplateController::class, 'update'])->name('update');
        Route::delete('/{invoiceTemplate}', [InvoiceTemplateController::class, 'destroy'])->name('destroy');
        Route::get('/{invoiceTemplate}/preview', [InvoiceTemplateController::class, 'preview'])->name('preview');
    });

    // System Settings (Admin/Owner level)
    Route::prefix('settings')->name('settings.')->middleware('permission:settings.view')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');

        // Company settings require organization update permission
        Route::match(['GET', 'POST'], '/company', [SettingsController::class, 'company'])
            ->name('company')
            ->middleware('permission:organization.update');

        Route::match(['GET', 'POST'], '/invoice', [SettingsController::class, 'invoice'])
            ->name('invoice')
            ->middleware('permission:settings.update');

        Route::match(['GET', 'POST'], '/email', [SettingsController::class, 'email'])
            ->name('email')
            ->middleware('permission:settings.update');

        Route::match(['GET', 'POST'], '/payment', [SettingsController::class, 'payment'])
            ->name('payment')
            ->middleware('permission:settings.update');

        Route::post('/test-email', [SettingsController::class, 'testEmail'])
            ->name('test-email')
            ->middleware('permission:settings.update');

        Route::post('/backup', [SettingsController::class, 'backup'])
            ->name('backup')
            ->middleware('permission:settings.update');

        Route::post('/clear-cache', [SettingsController::class, 'clearCache'])
            ->name('clear-cache')
            ->middleware('permission:settings.update');
    });
});