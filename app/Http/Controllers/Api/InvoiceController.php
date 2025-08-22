<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\InvoiceItem;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with(['client', 'items', 'payments']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('date_from')) {
            $query->where('issue_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = min($request->get('per_page', 15), 100); // Max 100 per page
        $invoices = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'issue_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:issue_date',
                'currency' => 'nullable|string|size:3|exists:currencies,code',
                'tax_rate' => 'nullable|numeric|min:0|max:100',
                'discount' => 'nullable|numeric|min:0',
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
                'currency' => $validated['currency'] ?? 'USD',
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

            $invoice->calculateTotals();
            $invoice->load(['client', 'items']);

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
                'data' => $invoice,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load(['client', 'items', 'payments']);

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft invoices can be updated.',
            ], 400);
        }

        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'issue_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:issue_date',
                'currency' => 'nullable|string|size:3|exists:currencies,code',
                'tax_rate' => 'nullable|numeric|min:0|max:100',
                'discount' => 'nullable|numeric|min:0',
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
                ]);
            }

            $invoice->calculateTotals();
            $invoice->load(['client', 'items']);

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully.',
                'data' => $invoice,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Paid invoices cannot be deleted.',
            ], 400);
        }

        try {
            $invoice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete invoice.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send the invoice to client.
     */
    public function send(Invoice $invoice): JsonResponse
    {
        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft invoices can be sent.',
            ], 400);
        }

        try {
            $this->invoiceService->sendInvoiceEmail($invoice);

            return response()->json([
                'success' => true,
                'message' => 'Invoice sent successfully.',
                'data' => $invoice->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invoice.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark invoice as paid.
     */
    public function markPaid(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid.',
            ], 400);
        }

        try {
            $validated = $request->validate([
                'payment_date' => 'nullable|date',
                'amount' => 'nullable|numeric|min:0.01',
                'method' => 'nullable|in:cash,check,bank_transfer,credit_card,paypal,other',
                'reference' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            // Create payment record
            $invoice->payments()->create([
                'amount' => $validated['amount'] ?? $invoice->remaining_balance,
                'payment_date' => $validated['payment_date'] ?? now()->toDateString(),
                'method' => $validated['method'] ?? 'other',
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice marked as paid successfully.',
                'data' => $invoice->fresh(['payments']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark invoice as paid.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get invoice statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'client_id' => 'nullable|exists:clients,id',
            'currency' => 'nullable|string|size:3|exists:currencies,code',
        ]);

        $stats = $this->invoiceService->getInvoiceStats($validated);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Convert invoice currency.
     */
    public function convertCurrency(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'to_currency' => 'required|string|size:3|exists:currencies,code',
        ]);

        try {
            $conversion = $this->invoiceService->convertCurrency($invoice, $validated['to_currency']);

            return response()->json([
                'success' => true,
                'data' => $conversion,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Currency conversion failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
