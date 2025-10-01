<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice); // User must have access to the invoice
        $this->authorize('create', Payment::class);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:'.$invoice->remaining_balance,
            'payment_date' => 'required|date',
            'method' => 'required|in:cash,check,bank_transfer,credit_card,paypal,other',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'method' => $validated['method'],
            'reference' => $validated['reference'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Payment recorded successfully.');
    }

    public function destroy(Payment $payment)
    {
        $this->authorize('delete', $payment);

        $invoice = $payment->invoice;
        $payment->delete();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Payment deleted successfully.');
    }
}
