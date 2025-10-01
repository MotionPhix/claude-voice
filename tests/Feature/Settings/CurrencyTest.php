<?php

use App\Models\Currency;
use App\Services\CurrencyService;

it('can manage currencies through settings', function () {
    $user = \App\Models\User::factory()->create();

    // Test creating a currency
    $response = $this->actingAs($user)->post('/settings/currencies', [
        'code' => 'EUR',
        'name' => 'Euro',
        'symbol' => '€',
        'exchange_rate' => 0.85,
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('currencies', [
        'code' => 'EUR',
        'name' => 'Euro',
        'symbol' => '€',
    ]);
});

it('can get active currencies through service', function () {
    Currency::factory()->create(['code' => 'USD', 'is_base' => true, 'is_active' => true]);
    Currency::factory()->create(['code' => 'EUR', 'is_active' => true]);
    Currency::factory()->create(['code' => 'GBP', 'is_active' => false]);

    $activeCurrencies = CurrencyService::getActiveCurrencies();

    expect($activeCurrencies)->toHaveCount(2);
    expect($activeCurrencies->pluck('code')->toArray())->toBe(['USD', 'EUR']);
});

it('can get base currency through service', function () {
    Currency::factory()->create(['code' => 'USD', 'is_base' => true]);
    Currency::factory()->create(['code' => 'EUR', 'is_base' => false]);

    $baseCurrency = CurrencyService::getBaseCurrency();

    expect($baseCurrency->code)->toBe('USD');
    expect($baseCurrency->is_base)->toBeTrue();
});

it('can format currency amounts', function () {
    Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'is_base' => true]);

    $formatted = CurrencyService::formatAmount(1234.56, 'USD');

    expect($formatted)->toBe('$1,234.56');
});

it('can convert between currencies', function () {
    Currency::factory()->create(['code' => 'USD', 'is_base' => true, 'exchange_rate' => 1]);
    Currency::factory()->create(['code' => 'EUR', 'is_base' => false, 'exchange_rate' => 0.85]);

    $converted = CurrencyService::convertAmount(100, 'USD', 'EUR');

    expect($converted)->toBe(85.0);
});

it('cannot delete base currency', function () {
    $user = \App\Models\User::factory()->create();
    $baseCurrency = Currency::factory()->create(['is_base' => true]);

    $response = $this->actingAs($user)->delete("/settings/currencies/{$baseCurrency->id}");

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertModelExists($baseCurrency);
});

it('can set new base currency', function () {
    $user = \App\Models\User::factory()->create();
    $oldBase = Currency::factory()->create(['code' => 'USD', 'is_base' => true]);
    $newBase = Currency::factory()->create(['code' => 'EUR', 'is_base' => false, 'is_active' => true]);

    $response = $this->actingAs($user)->patch("/settings/currencies/{$newBase->id}/set-base");

    $response->assertRedirect();

    $oldBase->refresh();
    $newBase->refresh();

    expect($oldBase->is_base)->toBeFalse();
    expect($newBase->is_base)->toBeTrue();
    expect($newBase->exchange_rate)->toBe(1.0);
});

it('validates currency codes are unique', function () {
    $user = \App\Models\User::factory()->create();
    Currency::factory()->create(['code' => 'USD']);

    $response = $this->actingAs($user)->post('/settings/currencies', [
        'code' => 'USD', // Duplicate
        'name' => 'US Dollar',
        'symbol' => '$',
        'exchange_rate' => 1,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('code');
});
