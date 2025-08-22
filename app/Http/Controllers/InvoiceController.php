<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
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
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function($clientQuery) use ($search) {
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

        return view('invoices.index', compact('invoices', 'clients'));
    }

    public function create()
    {
        $clients = Client::where('is_active', true)->get();
        return view('invoices.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'tax_rate' => 'numeric|min:0|max:100',
            'discount' => 'numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice = Invoice::create([
            'client_id' => $validated['client_id'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
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
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments']);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $clients = Client::where('is_active', true)->get();
        $invoice->load('items');

        return view('invoices.edit', compact('invoice', 'clients'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'tax_rate' => 'numeric|min:0|max:100',
            'discount' => 'numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice->update([
            'client_id' => $validated['client_id'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
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
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
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
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be sent.');
        }

        $invoice->markAsSent();

        // Here you would typically send the invoice via email
        // Mail::to($invoice->client->email)->send(new InvoiceMail($invoice));

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice sent successfully.');
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments']);

        // Using DomPDF (composer require barryvdh/laravel-dompdf)
        $pdf = PDF::loadView('invoices.pdf', compact('invoice'));

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function duplicate(Invoice $invoice)
    {
        $newInvoice = $invoice->replicate([
            'invoice_number', 'status', 'sent_at', 'paid_at'
        ]);

        $newInvoice->issue_date = now()->toDateString();
        $newInvoice->due_date = now()->addDays(30)->toDateString();
        $newInvoice->save();

        foreach ($invoice->items as $item) {
            $newItem = $item->replicate(['invoice_id']);
            $newItem->invoice_id = $newInvoice->id;
            $newItem->save();
        }

        return redirect()->route('invoices.edit', $newInvoice)
            ->with('success', 'Invoice duplicated successfully.');
    }
}
