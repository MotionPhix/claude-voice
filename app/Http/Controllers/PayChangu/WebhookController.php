<?php

namespace App\Http\Controllers\PayChangu;

use App\Http\Controllers\Controller;
use App\Services\PayChangu\PayChanguService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(protected PayChanguService $paychangu)
    {
    }

    /**
     * Handle PayChangu webhook
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Signature');

        // Verify webhook signature
        if (!$this->paychangu->verifyWebhookSignature($payload, $signature)) {
            Log::warning('PayChangu webhook signature verification failed', [
                'signature' => $signature,
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        // Decode payload
        $data = json_decode($payload, true);

        // Log webhook for debugging
        Log::info('PayChangu webhook received', [
            'event_type' => $data['event_type'] ?? 'unknown',
            'tx_ref' => $data['tx_ref'] ?? null,
            'status' => $data['status'] ?? null,
        ]);

        try {
            // Process the webhook
            $this->paychangu->processWebhook($data);

            // Return 200 to acknowledge receipt
            return response()->json(['message' => 'Webhook processed'], 200);
        } catch (\Exception $e) {
            Log::error('PayChangu webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $data,
            ]);

            // Still return 200 to prevent retries for application errors
            return response()->json(['message' => 'Webhook received'], 200);
        }
    }

    /**
     * Handle callback redirect from PayChangu
     */
    public function callback(Request $request)
    {
        $txRef = $request->query('tx_ref');
        $status = $request->query('status');

        if (!$txRef) {
            return redirect()->route('dashboard')->with('error', 'Invalid payment callback');
        }

        // Verify the transaction with PayChangu API
        $verificationData = $this->paychangu->verifyPayment($txRef);

        if (empty($verificationData)) {
            return redirect()->route('dashboard')->with('error', 'Payment verification failed');
        }

        // Get the invoice from meta data
        $invoiceId = $verificationData['meta']['invoice_id'] ?? null;

        if ($verificationData['status'] === 'success') {
            return redirect()
                ->route('invoices.show', $invoiceId)
                ->with('success', 'Payment successful! Thank you for your payment.');
        }

        return redirect()
            ->route('invoices.show', $invoiceId)
            ->with('error', 'Payment failed. Please try again.');
    }
}
