# PayChangu Integration Setup Guide

This guide will help you set up PayChangu payment gateway integration for the invoice system.

## Current Status

✅ **Structure Prepared** - The payment system is ready for PayChangu integration
🔧 **Integration Pending** - Routes and frontend components need to be added

## What's Already Done

### 1. Database Structure
- ✅ Payment table updated with PayChangu fields:
  - `gateway` - payment gateway type (manual, paychangu, etc.)
  - `status` - payment status (pending, processing, completed, failed, refunded)
  - `tx_ref` - unique PayChangu transaction reference
  - `gateway_reference` - PayChangu internal reference
  - `channel` - payment channel (Card, Mobile Money, Bank Transfer)
  - `gateway_response` - full webhook/API response
  - `customer_details` - customer information from gateway
  - `completed_at` / `failed_at` - status timestamps

### 2. Enums Created
- ✅ `PaymentStatus` enum (app/Enums/PaymentStatus.php)
- ✅ `PaymentGateway` enum (app/Enums/PaymentGateway.php)

### 3. Service Class
- ✅ `PayChanguService` class (app/Services/PayChangu/PayChanguService.php)
  - `initiatePayment()` - Create payment transaction
  - `verifyPayment()` - Verify transaction status
  - `verifyWebhookSignature()` - Validate webhook authenticity
  - `processWebhook()` - Handle webhook events
  - `getBalance()` - Check PayChangu wallet balance

### 4. Webhook Controller
- ✅ `WebhookController` (app/Http/Controllers/PayChangu/WebhookController.php)
  - Webhook handler with signature verification
  - Callback handler for payment redirects

### 5. Configuration
- ✅ PayChangu config added to `config/services.php`
- ✅ Environment variables in `.env.example`

## What's Needed to Complete Integration

### 1. Add Routes (web.php)

```php
use App\Http\Controllers\PayChangu\WebhookController;

// PayChangu routes
Route::post('/webhooks/paychangu', [WebhookController::class, 'handle'])->name('paychangu.webhook');
Route::get('/paychangu/callback', [WebhookController::class, 'callback'])->name('paychangu.callback');

// Payment initiation (add to invoice routes)
Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'initiatePayment'])->name('invoices.pay');
```

### 2. Add Invoice Controller Method

```php
use App\Services\PayChangu\PayChanguService;

public function initiatePayment(Invoice $invoice, PayChanguService $paychangu)
{
    // Authorize
    $this->authorize('pay', $invoice);

    // Initiate payment
    $result = $paychangu->initiatePayment($invoice, [
        'email' => auth()->user()->email,
        'first_name' => auth()->user()->name,
    ]);

    if ($result['success']) {
        return response()->json([
            'checkout_url' => $result['checkout_url'],
        ]);
    }

    return response()->json([
        'message' => $result['message'],
    ], 400);
}
```

### 3. Add Frontend Payment Button (Invoice View)

```vue
<script setup>
import { router } from '@inertiajs/vue3'

const initiatePayment = async (invoice) => {
  const response = await fetch(`/invoices/${invoice.id}/pay`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
  })

  const data = await response.json()

  if (data.checkout_url) {
    window.location.href = data.checkout_url
  }
}
</script>

<template>
  <button @click="initiatePayment(invoice)" v-if="invoice.status !== 'paid'">
    Pay with PayChangu
  </button>
</template>
```

### 4. Environment Setup

Add to your `.env` file:

```env
PAYCHANGU_BASE_URL=https://api.paychangu.com
PAYCHANGU_PUBLIC_KEY=your_public_key_here
PAYCHANGU_SECRET_KEY=your_secret_key_here
PAYCHANGU_WEBHOOK_SECRET=your_webhook_secret_here
```

### 5. Configure PayChangu Dashboard

1. **Get API Keys**: https://dashboard.paychangu.com/settings/api
2. **Set Webhook URL**: `https://yourdomain.com/webhooks/paychangu`
3. **Generate Webhook Secret**: Copy to `.env`

## Testing

### Test Credentials (from PayChangu docs)

- **Test Card**: `4242 4242 4242 4242`
- **Airtel Money**: `990000000`
- **Any CVV**: `123`
- **Any Future Date**: `12/25`

### Testing Flow

1. Create an invoice
2. Click "Pay with PayChangu"
3. Use test credentials
4. Verify webhook receives payment
5. Check payment status updates

## Payment Flow Diagram

```
1. User clicks "Pay with PayChangu"
2. System creates pending payment & gets checkout URL
3. User redirected to PayChangu checkout
4. User completes payment
5. PayChangu sends webhook to /webhooks/paychangu
6. System verifies webhook signature
7. System updates payment status
8. System redirects user to /paychangu/callback
9. User sees success/failure message
```

## Webhook Security

The webhook controller automatically:
- ✅ Verifies HMAC signature
- ✅ Logs all webhook events
- ✅ Returns 200 to prevent retries
- ✅ Re-verifies transaction via API

## Support Currencies

PayChangu currently supports:
- MWK (Malawian Kwacha)
- USD (US Dollar)

Make sure your invoices use supported currencies.

## Development Data

The `DevelopmentSeeder` now creates:
- Manual payments (gateway: manual)
- Simulated PayChangu payments (gateway: paychangu)
- Mix of payment channels (Card, Mobile Money, Bank Transfer)

## Next Steps

1. Add routes to `routes/web.php`
2. Add `initiatePayment` method to `InvoiceController`
3. Add payment button to invoice view
4. Configure PayChangu dashboard webhook
5. Test with PayChangu test credentials
6. Deploy to production with live keys

## Resources

- [PayChangu Documentation](https://developer.paychangu.com)
- [Inline Checkout Guide](https://developer.paychangu.com/docs/inline-popup)
- [Webhook Guide](https://developer.paychangu.com/docs/webhooks)
- [Test Credentials](https://developer.paychangu.com/docs/test)
