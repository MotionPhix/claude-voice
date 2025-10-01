<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::orderBy('is_base', 'desc')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return Inertia::render('settings/Currencies', [
            'currencies' => $currencies,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001|max:999999.999999',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_base'] = false;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['last_updated_at'] = now();

        Currency::create($validated);

        // Clear currency cache
        CurrencyService::clearCache();

        return redirect()->back()
            ->with('success', 'Currency added successfully.');
    }

    public function update(Request $request, Currency $currency)
    {
        // Prevent updating base currency code
        $codeRule = $currency->is_base
            ? 'required|string|size:3'
            : ['required', 'string', 'size:3', Rule::unique('currencies')->ignore($currency->id)];

        $validated = $request->validate([
            'code' => $codeRule,
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001|max:999999.999999',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['last_updated_at'] = now();

        // Base currency exchange rate must be 1
        if ($currency->is_base) {
            $validated['exchange_rate'] = 1.000000;
        }

        $currency->update($validated);

        // Clear currency cache
        CurrencyService::clearCache();

        return redirect()->back()
            ->with('success', 'Currency updated successfully.');
    }

    public function toggleStatus(Currency $currency)
    {
        // Cannot deactivate base currency
        if ($currency->is_base && $currency->is_active) {
            return redirect()->back()
                ->with('error', 'Cannot deactivate the base currency.');
        }

        $currency->update([
            'is_active' => ! $currency->is_active,
        ]);

        // Clear currency cache
        CurrencyService::clearCache();

        $status = $currency->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Currency {$status} successfully.");
    }

    public function setBase(Currency $currency)
    {
        if (! $currency->is_active) {
            return redirect()->back()
                ->with('error', 'Cannot set inactive currency as base currency.');
        }

        // Update all currencies
        Currency::query()->update(['is_base' => false]);

        $currency->update([
            'is_base' => true,
            'exchange_rate' => 1.000000,
            'is_active' => true,
        ]);

        // Clear currency cache
        CurrencyService::clearCache();

        return redirect()->back()
            ->with('success', 'Base currency updated successfully.');
    }

    public function destroy(Currency $currency)
    {
        // Cannot delete base currency
        if ($currency->is_base) {
            return redirect()->back()
                ->with('error', 'Cannot delete the base currency.');
        }

        // Check if currency is in use
        $invoicesCount = $currency->invoices()->count();
        $clientsCount = $currency->clients()->count();

        if ($invoicesCount > 0 || $clientsCount > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete currency that is being used by invoices or clients.');
        }

        $currency->delete();

        // Clear currency cache
        CurrencyService::clearCache();

        return redirect()->back()
            ->with('success', 'Currency deleted successfully.');
    }

    public function updateExchangeRates()
    {
        // This would typically fetch from an external API
        // For now, we'll just update the last_updated_at timestamp
        Currency::where('is_active', true)
            ->where('is_base', false)
            ->update(['last_updated_at' => now()]);

        // Clear currency cache
        CurrencyService::clearCache();

        return redirect()->back()
            ->with('success', 'Exchange rates updated successfully.');
    }

    public function bulkToggleStatus(Request $request)
    {
        $validated = $request->validate([
            'currency_ids' => 'required|array',
            'currency_ids.*' => 'exists:currencies,id',
            'action' => 'required|in:activate,deactivate',
        ]);

        $currencies = Currency::whereIn('id', $validated['currency_ids'])->get();

        $baseCurrencyInSelection = $currencies->contains('is_base', true);

        if ($validated['action'] === 'deactivate' && $baseCurrencyInSelection) {
            return redirect()->back()
                ->with('error', 'Cannot deactivate the base currency.');
        }

        $isActive = $validated['action'] === 'activate';

        Currency::whereIn('id', $validated['currency_ids'])
            ->update(['is_active' => $isActive]);

        // Clear currency cache
        CurrencyService::clearCache();

        $count = count($validated['currency_ids']);
        $status = $isActive ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "{$count} currencies {$status} successfully.");
    }
}
