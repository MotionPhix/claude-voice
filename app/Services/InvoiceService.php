<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Currency;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function generatePdf(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments', 'currency']);

        $pdf = PDF::loadView('invoices.pdf', compact('invoice'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    public function sendInvoiceEmail(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            throw new \Exception('Only draft invoices can be sent.');
        }

        // Generate PDF
        $pdf = $this->generatePdf($invoice);
        $pdfContent = $pdf->output();

        // Store PDF temporarily
        $fileName = "invoice-{$invoice->invoice_number}.pdf";
        Storage::put("temp/{$fileName}", $pdfContent);

        try {
            // Send email with PDF attachment
            Mail::to($invoice->client->email)->send(new \App\Mail\InvoiceMail($invoice, $fileName));

            // Mark as sent
            $invoice->markAsSent();

            return true;
        } finally {
            // Clean up temporary file
            Storage::delete("temp/{$fileName}");
        }
    }

    public function getInvoiceStats(array $filters = [])
    {
        $query = Invoice::query();

        if (isset($filters['date_from'])) {
            $query->where('issue_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('issue_date', '<=', $filters['date_to']);
        }

        if (isset($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (isset($filters['currency'])) {
            $query->where('currency', $filters['currency']);
        }

        $stats = [
            'total_count' => $query->count(),
            'total_amount' => $query->sum('total'),
            'paid_amount' => $query->where('status', 'paid')->sum('total'),
            'pending_amount' => $query->whereIn('status', ['sent', 'overdue'])->sum('total'),
            'overdue_count' => $query->where('status', 'overdue')->count(),
            'draft_count' => $query->where('status', 'draft')->count(),
        ];

        // Add currency breakdown
        $currencyStats = Invoice::selectRaw('currency, COUNT(*) as count, SUM(total) as total')
            ->when(isset($filters['date_from']), function($q) use ($filters) {
                return $q->where('issue_date', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function($q) use ($filters) {
                return $q->where('issue_date', '<=', $filters['date_to']);
            })
            ->groupBy('currency')
            ->get();

        $stats['by_currency'] = $currencyStats;

        return $stats;
    }

    public function getOverdueInvoices()
    {
        return Invoice::overdue()
            ->with(['client', 'currency'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function getDueToday()
    {
        return Invoice::dueToday()
            ->with(['client', 'currency'])
            ->get();
    }

    public function getDueSoon($days = 7)
    {
        return Invoice::dueSoon($days)
            ->with(['client', 'currency'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function convertCurrency(Invoice $invoice, $toCurrency)
    {
        if ($invoice->currency === $toCurrency) {
            return $invoice;
        }

        $fromCurrencyModel = Currency::where('code', $invoice->currency)->first();
        $toCurrencyModel = Currency::where('code', $toCurrency)->first();

        if (!$fromCurrencyModel || !$toCurrencyModel) {
            throw new \Exception('Invalid currency conversion requested.');
        }

        $convertedTotal = $fromCurrencyModel->convertTo($invoice->total, $toCurrency);
        $convertedSubtotal = $fromCurrencyModel->convertTo($invoice->subtotal, $toCurrency);
        $convertedTaxAmount = $fromCurrencyModel->convertTo($invoice->tax_amount, $toCurrency);
        $convertedDiscount = $fromCurrencyModel->convertTo($invoice->discount, $toCurrency);

        return (object) [
            'original_currency' => $invoice->currency,
            'converted_currency' => $toCurrency,
            'exchange_rate' => $toCurrencyModel->exchange_rate / $fromCurrencyModel->exchange_rate,
            'original_total' => $invoice->total,
            'converted_total' => round($convertedTotal, 2),
            'converted_subtotal' => round($convertedSubtotal, 2),
            'converted_tax_amount' => round($convertedTaxAmount, 2),
            'converted_discount' => round($convertedDiscount, 2),
        ];
    }

    public function markAsOverdue()
    {
        $count = Invoice::where('status', 'sent')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        return $count;
    }

    public function generateInvoiceNumber($customPrefix = null)
    {
        $prefix = $customPrefix ?: 'INV-' . date('Y') . '-';

        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, strlen($prefix)));
            return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '0001';
    }
}
