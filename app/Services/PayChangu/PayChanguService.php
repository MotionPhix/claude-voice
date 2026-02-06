<?php

namespace App\Services\PayChangu;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PayChanguService
{
    protected string $baseUrl;
    protected string $secretKey;
    protected string $publicKey;
    protected string $webhookSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.paychangu.base_url', 'https://api.paychangu.com');
        $this->secretKey = config('services.paychangu.secret_key');
        $this->publicKey = config('services.paychangu.public_key');
        $this->webhookSecret = config('services.paychangu.webhook_secret');
    }

    /**
     * Initiate a payment transaction
     */
    public function initiatePayment(Invoice $invoice, array $customerDetails = []): array
    {
        $txRef = $this->generateTransactionReference($invoice);

        $payload = [
            'amount' => $invoice->total,
            'currency' => $invoice->currency,
            'email' => $customerDetails['email'] ?? $invoice->client->email,
            'first_name' => $customerDetails['first_name'] ?? $invoice->client->name,
            'last_name' => $customerDetails['last_name'] ?? '',
            'callback_url' => route('paychangu.callback'),
            'return_url' => route('invoices.show', $invoice),
            'tx_ref' => $txRef,
            'customization' => [
                'title' => "Invoice #{$invoice->invoice_number}",
                'description' => "Payment for Invoice #{$invoice->invoice_number}",
            ],
            'meta' => [
                'invoice_id' => $invoice->id,
                'organization_id' => $invoice->organization_id,
            ],
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->secretKey}",
        ])->post("{$this->baseUrl}/payment", $payload);

        if ($response->successful()) {
            $data = $response->json('data');

            // Create pending payment record
            Payment::create([
                'invoice_id' => $invoice->id,
                'organization_id' => $invoice->organization_id,
                'amount' => $invoice->total,
                'currency' => $invoice->currency,
                'payment_date' => now(),
                'method' => 'credit_card',
                'gateway' => 'paychangu',
                'status' => 'pending',
                'tx_ref' => $txRef,
                'gateway_reference' => $data['tx_ref'] ?? null,
                'gateway_response' => $data,
            ]);

            return [
                'success' => true,
                'checkout_url' => $data['checkout_url'],
                'tx_ref' => $txRef,
            ];
        }

        return [
            'success' => false,
            'message' => $response->json('message', 'Payment initiation failed'),
            'errors' => $response->json('errors', []),
        ];
    }

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(string $txRef): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->secretKey}",
        ])->get("{$this->baseUrl}/verify-payment/{$txRef}");

        if ($response->successful()) {
            return $response->json('data');
        }

        return [];
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $computedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);

        return hash_equals($computedSignature, $signature);
    }

    /**
     * Process webhook payload
     */
    public function processWebhook(array $payload): void
    {
        $txRef = $payload['tx_ref'] ?? null;
        $status = $payload['status'] ?? null;
        $eventType = $payload['event_type'] ?? null;

        if (!$txRef) {
            return;
        }

        $payment = Payment::where('tx_ref', $txRef)->first();

        if (!$payment) {
            return;
        }

        // Update payment based on status
        if ($status === 'success') {
            $payment->update([
                'status' => 'completed',
                'gateway_reference' => $payload['reference'] ?? null,
                'channel' => $payload['authorization']['channel'] ?? null,
                'gateway_response' => $payload,
                'customer_details' => $payload['customer'] ?? null,
                'completed_at' => now(),
            ]);
        } elseif ($status === 'failed') {
            $payment->update([
                'status' => 'failed',
                'gateway_response' => $payload,
                'failed_at' => now(),
            ]);
        }
    }

    /**
     * Get wallet balance
     */
    public function getBalance(string $currency = 'MWK'): ?array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->secretKey}",
        ])->get("{$this->baseUrl}/wallet-balance", ['currency' => $currency]);

        if ($response->successful()) {
            return $response->json('data');
        }

        return null;
    }

    /**
     * Generate unique transaction reference
     */
    protected function generateTransactionReference(Invoice $invoice): string
    {
        return 'INV-' . $invoice->id . '-' . Str::upper(Str::random(8));
    }
}
