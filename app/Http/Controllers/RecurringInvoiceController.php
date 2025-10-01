<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\RecurringInvoice;
use App\Models\RecurringInvoiceItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecurringInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RecurringInvoice::class);

        $query = RecurringInvoice::with(['client', 'items']);

        if ($request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } else {
                $query->where('is_active', false);
            }
        }

        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $recurringInvoices = $query->orderBy('created_at', 'desc')->paginate(15);
        $clients = Client::where('is_active', true)->get();

        return Inertia::render('recurring-invoices/Index', [
            'recurringInvoices' => $recurringInvoices,
            'clients' => $clients,
            'filters' => $request->only(['status', 'client_id', 'search']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', RecurringInvoice::class);

        $clients = Client::where('is_active', true)->get();

        return Inertia::render('recurring-invoices/Create', [
            'clients' => $clients,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', RecurringInvoice::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'interval' => 'required|integer|min:1|max:99',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'max_cycles' => 'nullable|integer|min:1',
            'payment_terms_days' => 'required|integer|min:0|max:365',
            'tax_rate' => 'numeric|min:0|max:100',
            'discount' => 'numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $nextInvoiceDate = $this->calculateNextInvoiceDate($validated['start_date'], $validated['frequency'], $validated['interval']);

        $recurringInvoice = RecurringInvoice::create([
            'name' => $validated['name'],
            'client_id' => $validated['client_id'],
            'frequency' => $validated['frequency'],
            'interval' => $validated['interval'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'next_invoice_date' => $nextInvoiceDate,
            'max_cycles' => $validated['max_cycles'],
            'payment_terms_days' => $validated['payment_terms_days'],
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'discount' => $validated['discount'] ?? 0,
            'notes' => $validated['notes'],
            'terms' => $validated['terms'],
        ]);

        foreach ($validated['items'] as $item) {
            RecurringInvoiceItem::create([
                'recurring_invoice_id' => $recurringInvoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }

        $recurringInvoice->calculateTotals();

        return redirect()->route('recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Recurring invoice created successfully.');
    }

    public function show(RecurringInvoice $recurringInvoice)
    {
        $this->authorize('view', $recurringInvoice);

        $recurringInvoice->load(['client', 'items', 'invoices' => function ($query) {
            $query->latest()->with('payments');
        }]);

        return Inertia::render('recurring-invoices/Show', [
            'recurringInvoice' => $recurringInvoice,
        ]);
    }

    public function edit(RecurringInvoice $recurringInvoice)
    {
        $this->authorize('update', $recurringInvoice);

        $clients = Client::where('is_active', true)->get();
        $recurringInvoice->load('items');

        return Inertia::render('recurring-invoices/Edit', [
            'recurringInvoice' => $recurringInvoice,
            'clients' => $clients,
        ]);
    }

    public function update(Request $request, RecurringInvoice $recurringInvoice)
    {
        $this->authorize('update', $recurringInvoice);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'interval' => 'required|integer|min:1|max:99',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'max_cycles' => 'nullable|integer|min:1',
            'payment_terms_days' => 'required|integer|min:0|max:365',
            'tax_rate' => 'numeric|min:0|max:100',
            'discount' => 'numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Recalculate next invoice date if frequency or interval changed
        $shouldUpdateNextDate = $recurringInvoice->frequency !== $validated['frequency']
            || $recurringInvoice->interval !== $validated['interval'];

        $updateData = [
            'name' => $validated['name'],
            'client_id' => $validated['client_id'],
            'frequency' => $validated['frequency'],
            'interval' => $validated['interval'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'max_cycles' => $validated['max_cycles'],
            'payment_terms_days' => $validated['payment_terms_days'],
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'discount' => $validated['discount'] ?? 0,
            'notes' => $validated['notes'],
            'terms' => $validated['terms'],
            'is_active' => $validated['is_active'] ?? true,
        ];

        if ($shouldUpdateNextDate && $recurringInvoice->cycles_completed === 0) {
            $updateData['next_invoice_date'] = $this->calculateNextInvoiceDate(
                $validated['start_date'],
                $validated['frequency'],
                $validated['interval']
            );
        }

        $recurringInvoice->update($updateData);

        // Update items
        $recurringInvoice->items()->delete();
        foreach ($validated['items'] as $item) {
            RecurringInvoiceItem::create([
                'recurring_invoice_id' => $recurringInvoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }

        $recurringInvoice->calculateTotals();

        return redirect()->route('recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Recurring invoice updated successfully.');
    }

    public function destroy(RecurringInvoice $recurringInvoice)
    {
        $this->authorize('delete', $recurringInvoice);

        if ($recurringInvoice->invoices()->count() > 0) {
            return redirect()->route('recurring-invoices.index')
                ->with('error', 'Cannot delete recurring invoice with generated invoices.');
        }

        $recurringInvoice->delete();

        return redirect()->route('recurring-invoices.index')
            ->with('success', 'Recurring invoice deleted successfully.');
    }

    public function activate(RecurringInvoice $recurringInvoice)
    {
        $this->authorize('toggleStatus', $recurringInvoice);

        $recurringInvoice->update(['is_active' => true]);

        return redirect()->route('recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Recurring invoice activated successfully.');
    }

    public function deactivate(RecurringInvoice $recurringInvoice)
    {
        $this->authorize('toggleStatus', $recurringInvoice);

        $recurringInvoice->update(['is_active' => false]);

        return redirect()->route('recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Recurring invoice deactivated successfully.');
    }

    public function generateInvoice(RecurringInvoice $recurringInvoice)
    {
        $this->authorize('generateInvoice', $recurringInvoice);

        if (! $recurringInvoice->is_active) {
            return redirect()->route('recurring-invoices.show', $recurringInvoice)
                ->with('error', 'Cannot generate invoice from inactive recurring invoice.');
        }

        $invoice = $recurringInvoice->generateInvoice();

        if (! $invoice) {
            return redirect()->route('recurring-invoices.show', $recurringInvoice)
                ->with('error', 'Invoice generation failed. Check recurring invoice settings.');
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice generated successfully from recurring invoice.');
    }

    private function calculateNextInvoiceDate($startDate, $frequency, $interval)
    {
        $date = \Carbon\Carbon::parse($startDate);

        switch ($frequency) {
            case 'daily':
                return $date->addDays($interval);
            case 'weekly':
                return $date->addWeeks($interval);
            case 'monthly':
                return $date->addMonths($interval);
            case 'quarterly':
                return $date->addMonths(3 * $interval);
            case 'yearly':
                return $date->addYears($interval);
            default:
                return $date;
        }
    }
}
