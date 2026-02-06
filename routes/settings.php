<?php

use App\Http\Controllers\Settings\CurrencyController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/account/profile');

    // Account Settings
    Route::prefix('settings/account')->name('settings.account.')->group(function () {
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');
        Route::delete('profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.destroy');

        Route::get('security', [PasswordController::class, 'edit'])->name('security');
        Route::put('security/password', [PasswordController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('security.password');
        Route::delete('security/sessions/{session}', [PasswordController::class, 'destroySession'])->name('security.sessions.destroy');

        Route::get('notifications', function () {
            return Inertia::render('settings/account/Notifications', [
                'preferences' => [
                    'invoice_sent' => true,
                    'payment_received' => true,
                    'invoice_overdue' => true,
                    'weekly_summary' => false,
                    'marketing_emails' => false,
                    'browser_notifications' => true,
                    'desktop_notifications' => false,
                ],
            ]);
        })->name('notifications');
        Route::put('notifications', function () {
            // TODO: Implement notification preferences update
            return back();
        })->name('notifications.update');
    });

    // Organization Settings
    Route::prefix('settings/organization')->name('settings.organization.')->group(function () {
        Route::get('general', function () {
            return Inertia::render('settings/organization/General', [
                'organization' => current_organization(),
                'templates' => \App\Models\InvoiceTemplate::active()->get(),
            ]);
        })->name('general');
        Route::put('general', function (\Illuminate\Http\Request $request) {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|nullable|email|max:255',
                'phone' => 'sometimes|nullable|string|max:50',
                'website' => 'sometimes|nullable|url|max:255',
                'address' => 'sometimes|nullable|string|max:500',
                'city' => 'sometimes|nullable|string|max:100',
                'state' => 'sometimes|nullable|string|max:100',
                'postal_code' => 'sometimes|nullable|string|max:20',
                'country' => 'sometimes|nullable|string|max:100',
                'invoice_template_id' => 'sometimes|nullable|exists:invoice_templates,id',
            ]);

            current_organization()->update($validated);

            return back()->with('success', 'Organization settings updated successfully.');
        })->name('general.update');
        Route::post('general/logo', function () {
            // TODO: Implement logo upload
            return back();
        })->name('general.logo');
        Route::delete('general/logo', function () {
            // TODO: Implement logo deletion
            return back();
        })->name('general.logo.destroy');
        Route::delete('general', function () {
            // TODO: Implement organization deletion
            return back();
        })->name('general.destroy');

        Route::get('team', function () {
            return Inertia::render('settings/organization/Team', [
                'members' => [],
                'invitations' => [],
            ]);
        })->name('team');
        Route::post('team/invite', function () {
            // TODO: Implement team invitation
            return back();
        })->name('team.invite');
        Route::patch('team/members/{member}/role', function () {
            // TODO: Implement role update
            return back();
        })->name('team.members.role');
        Route::delete('team/members/{member}', function () {
            // TODO: Implement member removal
            return back();
        })->name('team.members.destroy');
        Route::delete('team/invitations/{invitation}', function () {
            // TODO: Implement invitation revocation
            return back();
        })->name('team.invitations.destroy');

        Route::get('branding', function () {
            return Inertia::render('settings/organization/Branding', [
                'branding' => [
                    'primary_color' => '#6366f1',
                    'secondary_color' => '#8b5cf6',
                    'invoice_template' => 'modern',
                    'invoice_footer' => '',
                ],
                'templates' => [
                    ['id' => 'modern', 'name' => 'Modern', 'preview' => '/images/templates/modern.png'],
                    ['id' => 'classic', 'name' => 'Classic', 'preview' => '/images/templates/classic.png'],
                    ['id' => 'minimal', 'name' => 'Minimal', 'preview' => '/images/templates/minimal.png'],
                ],
            ]);
        })->name('branding');
        Route::put('branding', function () {
            // TODO: Implement branding update
            return back();
        })->name('branding.update');

        Route::get('integrations', function () {
            return Inertia::render('settings/organization/Integrations', [
                'paychangu' => null,
                'apiKeys' => [],
            ]);
        })->name('integrations');
        Route::post('integrations/paychangu', function () {
            // TODO: Implement PayChangu configuration
            return back();
        })->name('integrations.paychangu');
        Route::post('integrations/api-keys', function () {
            // TODO: Implement API key creation
            return back();
        })->name('integrations.api-keys.store');
        Route::delete('integrations/api-keys/{key}', function () {
            // TODO: Implement API key revocation
            return back();
        })->name('integrations.api-keys.destroy');

        Route::get('currencies', [CurrencyController::class, 'index'])->name('currencies');
        Route::post('currencies', [CurrencyController::class, 'store'])->name('currencies.store');
        Route::put('currencies/{currency}', [CurrencyController::class, 'update'])->name('currencies.update');
        Route::patch('currencies/{currency}/toggle-status', [CurrencyController::class, 'toggleStatus'])->name('currencies.toggle-status');
        Route::patch('currencies/{currency}/set-base', [CurrencyController::class, 'setBase'])->name('currencies.set-base');
        Route::delete('currencies/{currency}', [CurrencyController::class, 'destroy'])->name('currencies.destroy');
        Route::patch('currencies/update-rates', [CurrencyController::class, 'updateExchangeRates'])->name('currencies.update-rates');
    });

    // Billing Settings
    Route::prefix('settings/billing')->name('settings.billing.')->group(function () {
        Route::get('subscription', function () {
            return Inertia::render('settings/billing/Subscription', [
                'currentPlan' => [
                    'id' => 'professional',
                    'name' => 'Professional',
                    'description' => 'Perfect for growing businesses',
                    'price' => 49,
                    'features' => [
                        'Unlimited invoices',
                        'Up to 10 team members',
                        'Multiple organizations',
                        'Priority support',
                    ],
                    'limits' => [
                        'invoices' => -1,
                        'members' => 10,
                        'organizations' => -1,
                    ],
                ],
                'availablePlans' => [
                    [
                        'id' => 'starter',
                        'name' => 'Starter',
                        'description' => 'For individuals and freelancers',
                        'price' => 19,
                        'features' => [
                            'Up to 100 invoices/month',
                            'Single user',
                            '1 organization',
                            'Email support',
                        ],
                        'limits' => [
                            'invoices' => 100,
                            'members' => 1,
                            'organizations' => 1,
                        ],
                    ],
                    [
                        'id' => 'professional',
                        'name' => 'Professional',
                        'description' => 'Perfect for growing businesses',
                        'price' => 49,
                        'features' => [
                            'Unlimited invoices',
                            'Up to 10 team members',
                            'Multiple organizations',
                            'Priority support',
                        ],
                        'limits' => [
                            'invoices' => -1,
                            'members' => 10,
                            'organizations' => -1,
                        ],
                    ],
                    [
                        'id' => 'enterprise',
                        'name' => 'Enterprise',
                        'description' => 'For large teams and companies',
                        'price' => 199,
                        'features' => [
                            'Unlimited everything',
                            'Unlimited team members',
                            'Custom integrations',
                            'Dedicated support',
                        ],
                        'limits' => [
                            'invoices' => -1,
                            'members' => -1,
                            'organizations' => -1,
                        ],
                    ],
                ],
                'usage' => [
                    'invoices' => 45,
                    'members' => 3,
                    'organizations' => 2,
                ],
            ]);
        })->name('subscription');
        Route::post('subscription/change', function () {
            // TODO: Implement plan change
            return back();
        })->name('subscription.change');

        Route::get('payment-methods', function () {
            return Inertia::render('settings/billing/PaymentMethods', [
                'paymentMethods' => [],
                'paychanguEnabled' => false,
                'billingInfo' => [
                    'name' => '',
                    'email' => '',
                    'tax_id' => '',
                ],
            ]);
        })->name('payment-methods');
        Route::put('payment-methods/info', function () {
            // TODO: Implement billing info update
            return back();
        })->name('payment-methods.update-info');
        Route::patch('payment-methods/{method}/default', function () {
            // TODO: Implement set default payment method
            return back();
        })->name('payment-methods.set-default');
        Route::delete('payment-methods/{method}', function () {
            // TODO: Implement payment method deletion
            return back();
        })->name('payment-methods.destroy');

        Route::get('invoices', function () {
            return Inertia::render('settings/billing/Invoices', [
                'billingInvoices' => [],
                'summary' => [
                    'totalPaid' => 0,
                    'nextPaymentDate' => now()->addMonth()->toDateString(),
                    'nextPaymentAmount' => 49,
                ],
                'preferences' => [
                    'email_invoices' => true,
                    'payment_reminders' => true,
                ],
            ]);
        })->name('invoices');
        Route::put('invoices/preferences', function () {
            // TODO: Implement invoice preferences update
            return back();
        })->name('invoices.preferences');
        Route::get('invoices/{invoice}', function () {
            // TODO: Implement invoice view
            return back();
        })->name('invoices.show');
        Route::get('invoices/{invoice}/download', function () {
            // TODO: Implement invoice download
            return back();
        })->name('invoices.download');
    });

    // Legacy routes - redirects to new structure
    Route::redirect('settings/profile', '/settings/account/profile');
    Route::redirect('settings/password', '/settings/account/security');
    Route::redirect('settings/currencies', '/settings/organization/currencies');
});
