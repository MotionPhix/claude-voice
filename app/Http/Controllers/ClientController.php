<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Traits\HasCurrency;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    use HasCurrency;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $query = Client::query();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $clients = $query->withCount('invoices')
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'active']),
        ]);
    }

    public function create(): \Inertia\Response
    {
        $this->authorize('create', Client::class);

        $currencies = $this->getCurrencyOptions();

        return Inertia::render('clients/Create', [
            'client' => new Client,
            'currencies' => $currencies,
            'defaultCurrency' => $this->getBaseCurrency()?->code ?? 'USD',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Client::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3|exists:currencies,code',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load(['invoices' => function ($query) {
            $query->with('payments')->latest();
        }]);

        return Inertia::render('clients/Show', [
            'client' => $client,
        ]);
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);

        $currencies = $this->getCurrencyOptions();

        return Inertia::render('clients/Edit', [
            'client' => $client,
            'currencies' => $currencies,
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,'.$client->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        if ($client->invoices()->count() > 0) {
            return redirect()->route('clients.index')
                ->with('error', 'Cannot delete client with existing invoices.');
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}
