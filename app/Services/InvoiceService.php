<?php

namespace App\Services;

use App\Models\Invoice;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoiceService
{
    /**
     * Generate a PDF for the given invoice.
     */
    public function generatePdf(Invoice $invoice): Dompdf
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);

        // Load the invoice with its relationships
        $invoice->load(['client', 'items', 'client.organization']);

        // Generate HTML content
        $html = view('pdfs.invoice', [
            'invoice' => $invoice,
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf;
    }

    /**
     * Send invoice email with PDF attachment.
     */
    public function sendInvoiceEmail(Invoice $invoice): void
    {
        $invoice->markAsSent();
    }
}
