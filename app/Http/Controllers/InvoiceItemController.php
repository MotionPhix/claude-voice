<?php

namespace App\Http\Controllers;

use App\Models\InvoiceItem;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class InvoiceItemController extends Controller
{
    /**
     * Display a listing of invoice items for a specific invoice.
     */
    public function index(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        $items = $invoice->items()->with('product')->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Store a newly created invoice item.
     */
    public function store(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        try {
            $validated = $request->validate([
                'product_id' => 'nullable|exists:products,id',
                'description' => 'required|string|max:500',
                'quantity' => 'required|numeric|min:0.01',
                'unit_price' => 'required|numeric|min:0',
                'tax_rate' => 'nullable|numeric|min:0|max:100'
            ]);

            $validated['invoice_id'] = $invoice->id;
            $validated['tax_rate'] = $validated['tax_rate'] ?? 0;

            // Calculate amounts
            $subtotal = $validated['quantity'] * $validated['unit_price'];
            $tax_amount = $subtotal * ($validated['tax_rate'] / 100);
            $total = $subtotal + $tax_amount;

            $validated['subtotal'] = round($subtotal, 2);
            $validated['tax_amount'] = round($tax_amount, 2);
            $validated['total'] = round($total, 2);

            $item = InvoiceItem::create($validated);

            // Update invoice totals
            $this->updateInvoiceTotals($invoice);

            $item->load('product');

            return response()->json([
                'success' => true,
                'message' => 'Invoice item created successfully',
                'data' => $item
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified invoice item.
     */
    public function show(Invoice $invoice, InvoiceItem $invoiceItem): JsonResponse
    {
        $this->authorize('view', $invoice);

        if ($invoiceItem->invoice_id !== $invoice->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice item not found'
            ], 404);
        }

        $invoiceItem->load('product');

        return response()->json([
            'success' => true,
            'data' => $invoiceItem
        ]);
    }

    /**
     * Update the specified invoice item.
     */
    public function update(Request $request, Invoice $invoice, InvoiceItem $invoiceItem): JsonResponse
    {
        $this->authorize('update', $invoice);

        if ($invoiceItem->invoice_id !== $invoice->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice item not found'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'product_id' => 'nullable|exists:products,id',
                'description' => 'required|string|max:500',
                'quantity' => 'required|numeric|min:0.01',
                'unit_price' => 'required|numeric|min:0',
                'tax_rate' => 'nullable|numeric|min:0|max:100'
            ]);

            $validated['tax_rate'] = $validated['tax_rate'] ?? 0;

            // Calculate amounts
            $subtotal = $validated['quantity'] * $validated['unit_price'];
            $tax_amount = $subtotal * ($validated['tax_rate'] / 100);
            $total = $subtotal + $tax_amount;

            $validated['subtotal'] = round($subtotal, 2);
            $validated['tax_amount'] = round($tax_amount, 2);
            $validated['total'] = round($total, 2);

            $invoiceItem->update($validated);

            // Update invoice totals
            $this->updateInvoiceTotals($invoice);

            $invoiceItem->load('product');

            return response()->json([
                'success' => true,
                'message' => 'Invoice item updated successfully',
                'data' => $invoiceItem
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified invoice item.
     */
    public function destroy(Invoice $invoice, InvoiceItem $invoiceItem): JsonResponse
    {
        $this->authorize('update', $invoice);

        if ($invoiceItem->invoice_id !== $invoice->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice item not found'
            ], 404);
        }

        $invoiceItem->delete();

        // Update invoice totals
        $this->updateInvoiceTotals($invoice);

        return response()->json([
            'success' => true,
            'message' => 'Invoice item deleted successfully'
        ]);
    }

    /**
     * Bulk operations on invoice items.
     */
    public function bulkOperation(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        try {
            $validated = $request->validate([
                'operation' => 'required|in:delete,duplicate',
                'item_ids' => 'required|array|min:1',
                'item_ids.*' => 'exists:invoice_items,id'
            ]);

            $items = InvoiceItem::whereIn('id', $validated['item_ids'])
                ->where('invoice_id', $invoice->id)
                ->get();

            if ($items->count() !== count($validated['item_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some items not found or do not belong to this invoice'
                ], 422);
            }

            switch ($validated['operation']) {
                case 'delete':
                    InvoiceItem::whereIn('id', $validated['item_ids'])->delete();
                    $message = 'Items deleted successfully';
                    break;

                case 'duplicate':
                    foreach ($items as $item) {
                        $newItem = $item->replicate();
                        $newItem->save();
                    }
                    $message = 'Items duplicated successfully';
                    break;
            }

            // Update invoice totals
            $this->updateInvoiceTotals($invoice);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Update invoice totals based on items.
     */
    private function updateInvoiceTotals(Invoice $invoice): void
    {
        $items = $invoice->items;

        $subtotal = $items->sum('subtotal');
        $taxAmount = $items->sum('tax_amount');
        $total = $items->sum('total');

        $invoice->update([
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'total' => round($total, 2)
        ]);
    }

    /**
     * Reorder invoice items.
     */
    public function reorder(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        try {
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|exists:invoice_items,id',
                'items.*.order' => 'required|integer|min:0'
            ]);

            foreach ($validated['items'] as $itemData) {
                InvoiceItem::where('id', $itemData['id'])
                    ->where('invoice_id', $invoice->id)
                    ->update(['order' => $itemData['order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Items reordered successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }
}
