<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceService;
use App\Traits\HasCurrency;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    use HasCurrency;

    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::with(['client', 'payments']);

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by client
        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Search functionality
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Date range filter
        if ($request->date_from) {
            $query->where('issue_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);
        $clients = Client::where('is_active', true)->get();

        // Calculate stats for the filtered invoices
        $statsQuery = Invoice::query();
        if ($request->status) {
            $statsQuery->where('status', $request->status);
        }
        if ($request->client_id) {
            $statsQuery->where('client_id', $request->client_id);
        }
        if ($request->search) {
            $statsQuery->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($request) {
                        $clientQuery->where('name', 'like', "%{$request->search}%");
                    });
            });
        }
        if ($request->date_from) {
            $statsQuery->where('issue_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $statsQuery->where('issue_date', '<=', $request->date_to);
        }

        $stats = [
            'total_amount' => $statsQuery->sum('total'),
            'paid_amount' => $statsQuery->where('status', 'paid')->sum('total'),
            'pending_amount' => $statsQuery->whereIn('status', ['sent', 'draft'])->sum('total'),
            'overdue_amount' => $statsQuery->where('status', 'overdue')->sum('total'),
            'total_count' => $statsQuery->count(),
        ];

        return Inertia::render('invoices/Index', [
            'invoices' => $invoices,
            'clients' => $clients,
            'filters' => $request->only(['status', 'client_id', 'search', 'date_from', 'date_to']),
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Invoice::class);

        $clients = Client::where('is_active', true)->get();
        $currencies = $this->getCurrencyOptions();

        return Inertia::render('invoices/Create', [
            'clients' => $clients,
            'currencies' => $currencies,
            'defaultCurrency' => $this->getBaseCurrency()?->code ?? 'USD',
        ]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validated();

        $invoice = Invoice::create([
            'client_id' => $validated['client_id'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'currency' => $validated['currency'] ?? $this->getBaseCurrency()?->code ?? 'USD',
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'discount' => $validated['discount'] ?? 0,
            'notes' => $validated['notes'],
            'terms' => $validated['terms'],
        ]);

        foreach ($validated['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        $invoice->calculateTotals();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'items', 'payments']);

        return Inertia::render('invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $clients = Client::where('is_active', true)->get();
        $currencies = $this->getCurrencyOptions();
        $invoice->load('items');

        return Inertia::render('invoices/Edit', [
            'invoice' => $invoice,
            'clients' => $clients,
            'currencies' => $currencies,
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $validated = $request->validated();

        $invoice->update([
            'client_id' => $validated['client_id'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'currency' => $validated['currency'] ?? $invoice->currency,
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'discount' => $validated['discount'] ?? 0,
            'notes' => $validated['notes'],
            'terms' => $validated['terms'],
        ]);

        // Update items
        $invoice->items()->delete();
        foreach ($validated['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        $invoice->calculateTotals();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        // Check business rules after authorization
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.index')
                ->with('error', 'Paid invoices cannot be deleted.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function send(Invoice $invoice)
    {
        $this->authorize('send', $invoice);

        // Check business rules after authorization
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be sent.');
        }

        try {
            $this->invoiceService->sendInvoiceEmail($invoice);

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice sent successfully to '.$invoice->client->email.'.');
        } catch (\Exception $e) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Failed to send invoice: '.$e->getMessage());
        }
    }

    public function duplicate(Invoice $invoice)
    {
        $this->authorize('duplicate', $invoice);

        $duplicate = $invoice->duplicate();

        return redirect()->route('invoices.edit', $duplicate)
            ->with('success', 'Invoice duplicated successfully as '.$duplicate->invoice_number.'.');
    }

    public function pdf(Invoice $invoice)
    {
        $this->authorize('downloadPdf', $invoice);

        $pdf = $this->invoiceService->generatePdf($invoice);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
