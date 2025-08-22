<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $pdfFileName;

    public function __construct(Invoice $invoice, $pdfFileName = null)
    {
        $this->invoice = $invoice;
        $this->pdfFileName = $pdfFileName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice {$this->invoice->invoice_number} from " . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'client' => $this->invoice->client,
                'company_name' => config('app.name'),
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->pdfFileName && Storage::exists("temp/{$this->pdfFileName}")) {
            $attachments[] = Attachment::fromStorage("temp/{$this->pdfFileName}")
                ->as($this->pdfFileName)
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
