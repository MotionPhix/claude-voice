<?php

namespace App\Traits;

use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Support\Collection;

trait HasCurrency
{
    /**
     * Get active currencies for forms/selects
     */
    protected function getActiveCurrencies(): Collection
    {
        return CurrencyService::getActiveCurrencies();
    }

    /**
     * Get currency options for select components
     */
    protected function getCurrencyOptions(): array
    {
        return CurrencyService::getCurrencySelectOptions();
    }

    /**
     * Get base currency
     */
    protected function getBaseCurrency(): ?Currency
    {
        return CurrencyService::getBaseCurrency();
    }

    /**
     * Format currency amount
     */
    protected function formatCurrency(float $amount, ?string $currencyCode = null): string
    {
        return CurrencyService::formatCurrency($amount, $currencyCode);
    }

    /**
     * Validate currency code
     */
    protected function isValidCurrency(string $currencyCode): bool
    {
        return CurrencyService::isValidCurrencyCode($currencyCode);
    }

    /**
     * Convert between currencies
     */
    protected function convertCurrency(float $amount, string $from, string $to): float
    {
        return CurrencyService::convertAmount($amount, $from, $to);
    }
}
