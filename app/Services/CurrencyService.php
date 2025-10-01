<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    /**
     * Get all active currencies (cached for performance)
     */
    public static function getActiveCurrencies(): Collection
    {
        return Cache::remember('active_currencies', 3600, function () {
            return Currency::where('is_active', true)
                ->orderBy('is_base', 'desc')
                ->orderBy('code')
                ->get();
        });
    }

    /**
     * Get all currencies (cached for performance)
     */
    public static function getAllCurrencies(): Collection
    {
        return Cache::remember('all_currencies', 3600, function () {
            return Currency::orderBy('is_base', 'desc')
                ->orderBy('is_active', 'desc')
                ->orderBy('code')
                ->get();
        });
    }

    /**
     * Get the base currency
     */
    public static function getBaseCurrency(): ?Currency
    {
        return Cache::remember('base_currency', 3600, function () {
            return Currency::where('is_base', true)->first();
        });
    }

    /**
     * Get currency by code
     */
    public static function getCurrencyByCode(string $code): ?Currency
    {
        return self::getAllCurrencies()->firstWhere('code', strtoupper($code));
    }

    /**
     * Get currency options for forms (code => name format)
     */
    public static function getCurrencyOptions(): array
    {
        return self::getActiveCurrencies()
            ->mapWithKeys(function ($currency) {
                return [$currency->code => "{$currency->code} - {$currency->name}"];
            })
            ->toArray();
    }

    /**
     * Get currency options for select components (value/label format)
     */
    public static function getCurrencySelectOptions(): array
    {
        return self::getActiveCurrencies()
            ->map(function ($currency) {
                return [
                    'value' => $currency->code,
                    'label' => "{$currency->code} - {$currency->name}",
                    'symbol' => $currency->symbol,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Format amount with currency
     */
    public static function formatAmount(float $amount, ?string $currencyCode = null): string
    {
        $currency = $currencyCode
            ? self::getCurrencyByCode($currencyCode)
            : self::getBaseCurrency();

        if (! $currency) {
            return number_format($amount, 2);
        }

        return $currency->symbol.number_format($amount, 2);
    }

    /**
     * Format amount for display (with proper currency formatting)
     */
    public static function formatCurrency(float $amount, ?string $currencyCode = null): string
    {
        $currency = $currencyCode
            ? self::getCurrencyByCode($currencyCode)
            : self::getBaseCurrency();

        if (! $currency) {
            $currencyCode = $currencyCode ?: 'USD';
        } else {
            $currencyCode = $currency->code;
        }

        // Use proper locale formatting
        return (new \NumberFormatter('en_US', \NumberFormatter::CURRENCY))
            ->formatCurrency($amount, $currencyCode);
    }

    /**
     * Convert amount from one currency to another
     */
    public static function convertAmount(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $fromCurrencyModel = self::getCurrencyByCode($fromCurrency);
        $toCurrencyModel = self::getCurrencyByCode($toCurrency);

        if (! $fromCurrencyModel || ! $toCurrencyModel) {
            throw new \InvalidArgumentException('Invalid currency code provided');
        }

        return $fromCurrencyModel->convertTo($amount, $toCurrency);
    }

    /**
     * Get exchange rate between two currencies
     */
    public static function getExchangeRate(string $fromCurrency, string $toCurrency): float
    {
        return self::convertAmount(1, $fromCurrency, $toCurrency);
    }

    /**
     * Check if currency is active
     */
    public static function isCurrencyActive(string $currencyCode): bool
    {
        $currency = self::getCurrencyByCode($currencyCode);

        return $currency ? $currency->is_active : false;
    }

    /**
     * Clear currency cache (call after any currency updates)
     */
    public static function clearCache(): void
    {
        Cache::forget('active_currencies');
        Cache::forget('all_currencies');
        Cache::forget('base_currency');
    }

    /**
     * Get popular currencies (for quick selection)
     */
    public static function getPopularCurrencies(): Collection
    {
        $popularCodes = ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'CNY'];

        return self::getActiveCurrencies()
            ->whereIn('code', $popularCodes)
            ->sortBy(function ($currency) use ($popularCodes) {
                return array_search($currency->code, $popularCodes);
            });
    }

    /**
     * Validate currency code
     */
    public static function isValidCurrencyCode(string $currencyCode): bool
    {
        return self::getCurrencyByCode($currencyCode) !== null;
    }

    /**
     * Get currency statistics
     */
    public static function getCurrencyStats(): array
    {
        $currencies = self::getAllCurrencies();

        return [
            'total' => $currencies->count(),
            'active' => $currencies->where('is_active', true)->count(),
            'inactive' => $currencies->where('is_active', false)->count(),
            'base_currency' => $currencies->firstWhere('is_base', true)?->code,
            'last_updated' => $currencies->max('last_updated_at') ?? $currencies->max('updated_at'),
        ];
    }
}
