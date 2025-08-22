<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        $query = Currency::query();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $currencies = $query->orderBy('code')->paginate(15);
        $baseCurrency = Currency::where('is_base', true)->first();

        return Inertia::render('currencies/Index', [
            'currencies' => $currencies,
            'baseCurrency' => $baseCurrency,
            'filters' => $request->only(['search', 'active']),
        ]);
    }

    public function create()
    {
        return Inertia::render('currencies/Create', [
            'currency' => new Currency(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies',
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Ensure only one base currency
        if ($validated['is_base'] ?? false) {
            Currency::where('is_base', true)->update(['is_base' => false]);
        }

        Currency::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'symbol' => $validated['symbol'],
            'exchange_rate' => $validated['exchange_rate'],
            'is_base' => $validated['is_base'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'last_updated_at' => now(),
        ]);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency created successfully.');
    }

    public function show(Currency $currency)
    {
        $currency->load(['clients', 'invoices']);

        return Inertia::render('currencies/Show', [
            'currency' => $currency,
        ]);
    }

    public function edit(Currency $currency)
    {
        return view('currencies.edit', compact('currency'));
    }

    public function update(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Ensure only one base currency
        if ($validated['is_base'] ?? false) {
            Currency::where('id', '!=', $currency->id)
                ->where('is_base', true)
                ->update(['is_base' => false]);
        }

        $currency->update([
            'name' => $validated['name'],
            'symbol' => $validated['symbol'],
            'exchange_rate' => $validated['exchange_rate'],
            'is_base' => $validated['is_base'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'last_updated_at' => now(),
        ]);

        return redirect()->route('currencies.show', $currency)
            ->with('success', 'Currency updated successfully.');
    }

    public function destroy(Currency $currency)
    {
        if ($currency->is_base) {
            return redirect()->route('currencies.index')
                ->with('error', 'Cannot delete the base currency.');
        }

        if ($currency->clients()->count() > 0 || $currency->invoices()->count() > 0) {
            return redirect()->route('currencies.index')
                ->with('error', 'Cannot delete currency that is in use by clients or invoices.');
        }

        $currency->delete();

        return redirect()->route('currencies.index')
            ->with('success', 'Currency deleted successfully.');
    }

    public function updateRates(Request $request)
    {
        try {
            $baseCurrency = Currency::where('is_base', true)->first();

            if (!$baseCurrency) {
                return redirect()->route('currencies.index')
                    ->with('error', 'No base currency set. Please set a base currency first.');
            }

            $currencies = Currency::where('is_active', true)
                ->where('is_base', false)
                ->get();

            $updatedCount = 0;
            $errors = [];

            foreach ($currencies as $currency) {
                try {
                    $rate = $this->fetchExchangeRate($baseCurrency->code, $currency->code);

                    if ($rate) {
                        $currency->update([
                            'exchange_rate' => $rate,
                            'last_updated_at' => now(),
                        ]);
                        $updatedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to update {$currency->code}: " . $e->getMessage();
                }
            }

            if ($updatedCount > 0) {
                $message = "Successfully updated {$updatedCount} exchange rates.";
                if (!empty($errors)) {
                    $message .= ' Some rates failed to update: ' . implode(', ', $errors);
                }
                return redirect()->route('currencies.index')->with('success', $message);
            } else {
                return redirect()->route('currencies.index')
                    ->with('error', 'No exchange rates were updated. ' . implode(', ', $errors));
            }

        } catch (\Exception $e) {
            return redirect()->route('currencies.index')
                ->with('error', 'Failed to update exchange rates: ' . $e->getMessage());
        }
    }

    public function setBase(Currency $currency)
    {
        // Remove base flag from all currencies
        Currency::where('is_base', true)->update(['is_base' => false]);

        // Set this currency as base with rate 1.0
        $currency->update([
            'is_base' => true,
            'exchange_rate' => 1.0,
            'last_updated_at' => now(),
        ]);

        return redirect()->route('currencies.index')
            ->with('success', "Set {$currency->code} as base currency successfully.");
    }

    private function fetchExchangeRate($fromCurrency, $toCurrency)
    {
        // Using a free exchange rate API (you might want to use a different service)
        $apiKey = config('services.exchange_rates.api_key'); // Add to config/services.php

        if (!$apiKey) {
            // Fallback to a free service without API key requirement
            $response = Http::get("https://api.exchangerate-api.com/v4/latest/{$fromCurrency}");

            if ($response->successful()) {
                $data = $response->json();
                return $data['rates'][$toCurrency] ?? null;
            }

            throw new \Exception('Failed to fetch exchange rate from API');
        }

        // Using a service that requires API key
        $response = Http::get("https://v6.exchangerate-api.com/v6/{$apiKey}/latest/{$fromCurrency}");

        if ($response->successful()) {
            $data = $response->json();

            if ($data['result'] === 'success') {
                return $data['conversion_rates'][$toCurrency] ?? null;
            }

            throw new \Exception('API returned error: ' . ($data['error-type'] ?? 'Unknown error'));
        }

        throw new \Exception('HTTP request failed with status: ' . $response->status());
    }

    public function convert(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'from_currency' => 'required|string|exists:currencies,code',
            'to_currency' => 'required|string|exists:currencies,code',
        ]);

        $fromCurrency = Currency::where('code', $validated['from_currency'])->first();
        $toCurrency = Currency::where('code', $validated['to_currency'])->first();

        if (!$fromCurrency || !$toCurrency) {
            return response()->json(['error' => 'Invalid currency codes'], 400);
        }

        $convertedAmount = $fromCurrency->convertTo($validated['amount'], $validated['to_currency']);

        return response()->json([
            'amount' => $validated['amount'],
            'from_currency' => $validated['from_currency'],
            'to_currency' => $validated['to_currency'],
            'converted_amount' => round($convertedAmount, 2),
            'exchange_rate' => round($convertedAmount / $validated['amount'], 6),
        ]);
    }
}
