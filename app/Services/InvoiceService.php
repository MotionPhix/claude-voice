<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    public function generatePdf(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'payments']);
        return PDF::loadView('invoices.pdf', compact('invoice'));
    }

    public function sendInvoiceEmail(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            throw new \Exception('Only draft invoices can be sent.');
        }

        $invoice->markAsSent();

        // Send email logic here
        // Mail::to($invoice->client->email)->send(new InvoiceMail($invoice));

        return true;
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

        return [
            'total_count' => $query->count(),
            'total_amount' => $query->sum('total'),
            'paid_amount' => $query->where('status', 'paid')->sum('total'),
            'pending_amount' => $query->whereIn('status', ['sent', 'overdue'])->sum('total'),
            'overdue_count' => $query->where('status', 'overdue')->count(),
        ];
    }
}
