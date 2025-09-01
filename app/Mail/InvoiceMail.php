<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    public string $pdfFileName;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, string $pdfFileName)
    {
        $this->invoice = $invoice;
        $this->pdfFileName = $pdfFileName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->invoice->client->email],
            subject: "Invoice #{$this->invoice->invoice_number} - {$this->getCompanyName()}",
            tags: ['invoice', 'billing'],
            metadata: [
                'invoice_id' => $this->invoice->id,
                'client_id' => $this->invoice->client_id,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'client' => $this->invoice->client,
                'companyName' => $this->getCompanyName(),
                'dueDate' => $this->invoice->due_date->format('F j, Y'),
                'formattedTotal' => '$' . number_format($this->invoice->total, 2),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (Storage::exists("temp/{$this->pdfFileName}")) {
            return [
                Attachment::fromStorageDisk('local', "temp/{$this->pdfFileName}")
                    ->as($this->pdfFileName)
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }

    /**
     * Get the company name from settings.
     */
    private function getCompanyName(): string
    {
        return config('app.name', 'Your Company');
    }
}
