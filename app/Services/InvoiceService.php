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
        $invoice->load(['client', 'items', 'organization', 'organization.invoiceTemplate']);

        // Get the organization's template or use default
        $template = $invoice->organization->invoiceTemplate
            ?? \App\Models\InvoiceTemplate::getDefault()
            ?? \App\Models\InvoiceTemplate::where('slug', 'default')->first();

        // Get currency symbol
        $currencySymbol = $this->getCurrencySymbol($invoice->currency);

        // Generate HTML content using the selected template
        $html = view($template->view_path, [
            'invoice' => $invoice,
            'organization' => $invoice->organization,
            'currencySymbol' => $currencySymbol,
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf;
    }

    /**
     * Get currency symbol for the given currency code.
     */
    protected function getCurrencySymbol(string $currencyCode): string
    {
        $currency = \App\Models\Currency::where('code', $currencyCode)->first();

        return $currency?->symbol ?? $currencyCode;
    }

    /**
     * Send invoice email with PDF attachment.
     */
    public function sendInvoiceEmail(Invoice $invoice): void
    {
        $invoice->markAsSent();
    }
}
