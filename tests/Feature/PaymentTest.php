<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can record payment for invoice', function () {
    $invoice = Invoice::factory()->create(['total' => 1000]);

    $paymentData = [
        'amount' => 500,
        'payment_date' => now()->toDateString(),
        'method' => 'bank_transfer',
        'reference' => 'TXN-12345',
        'notes' => 'Partial payment'
    ];

    $response = $this->post(route('payments.store', $invoice), $paymentData);

    $payment = Payment::latest()->first();
    
    expect($payment)
        ->invoice_id->toBe($invoice->id)
        ->amount->toBe(500.00)
        ->method->toBe('bank_transfer')
        ->reference->toBe('TXN-12345');

    $invoice->refresh();
    expect($invoice->amount_paid)->toBe(500.00);

    $response->assertRedirect(route('invoices.show', $invoice))
        ->assertSessionHas('success', 'Payment recorded successfully.');
});

test('cannot record payment exceeding remaining balance', function () {
    $invoice = Invoice::factory()->create(['total' => 1000]);
    Payment::factory()->for($invoice)->create(['amount' => 600]);

    $paymentData = [
        'amount' => 500, // Total would be 1100, exceeding invoice total
        'payment_date' => now()->toDateString(),
        'method' => 'cash'
    ];

    $response = $this->post(route('payments.store', $invoice), $paymentData);

    $response->assertSessionHasErrors(['amount']);
});

test('invoice status updates to paid when fully paid', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 1000]);

    $this->post(route('payments.store', $invoice), [
        'amount' => 1000,
        'payment_date' => now()->toDateString(),
        'method' => 'credit_card'
    ]);

    $invoice->refresh();
    
    expect($invoice)
        ->status->toBe('paid')
        ->amount_paid->toBe(1000.00)
        ->paid_at->not->toBeNull();
});

test('invoice status remains sent when partially paid', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 1000]);

    $this->post(route('payments.store', $invoice), [
        'amount' => 500,
        'payment_date' => now()->toDateString(),
        'method' => 'cash'
    ]);

    $invoice->refresh();
    
    expect($invoice)
        ->status->toBe('sent')
        ->amount_paid->toBe(500.00)
        ->paid_at->toBeNull();
});

test('can delete payment', function () {
    $invoice = Invoice::factory()->create(['total' => 1000]);
    $payment = Payment::factory()->for($invoice)->create(['amount' => 300]);

    // Initial state
    $invoice->refresh();
    expect($invoice->amount_paid)->toBe(300.00);

    $response = $this->delete(route('payments.destroy', $payment));

    expect(Payment::find($payment->id))->toBeNull();

    $invoice->refresh();
    expect($invoice->amount_paid)->toBe(0.00);

    $response->assertRedirect(route('invoices.show', $invoice))
        ->assertSessionHas('success', 'Payment deleted successfully.');
});

test('payment validation requires amount', function () {
    $invoice = Invoice::factory()->create();

    $response = $this->post(route('payments.store', $invoice), [
        'payment_date' => now()->toDateString(),
        'method' => 'cash'
        // missing amount
    ]);

    $response->assertSessionHasErrors(['amount']);
});

test('payment validation requires valid payment method', function () {
    $invoice = Invoice::factory()->create();

    $response = $this->post(route('payments.store', $invoice), [
        'amount' => 100,
        'payment_date' => now()->toDateString(),
        'method' => 'invalid_method'
    ]);

    $response->assertSessionHasErrors(['method']);
});

test('payment amount must be positive', function () {
    $invoice = Invoice::factory()->create();

    $response = $this->post(route('payments.store', $invoice), [
        'amount' => -50,
        'payment_date' => now()->toDateString(),
        'method' => 'cash'
    ]);

    $response->assertSessionHasErrors(['amount']);
});

test('payment date must be valid date', function () {
    $invoice = Invoice::factory()->create();

    $response = $this->post(route('payments.store', $invoice), [
        'amount' => 100,
        'payment_date' => 'invalid-date',
        'method' => 'cash'
    ]);

    $response->assertSessionHasErrors(['payment_date']);
});

test('multiple payments can be recorded for same invoice', function () {
    $invoice = Invoice::factory()->create(['total' => 1000]);

    // First payment
    $this->post(route('payments.store', $invoice), [
        'amount' => 300,
        'payment_date' => now()->subDays(5)->toDateString(),
        'method' => 'bank_transfer'
    ]);

    // Second payment
    $this->post(route('payments.store', $invoice), [
        'amount' => 200,
        'payment_date' => now()->subDays(2)->toDateString(),
        'method' => 'credit_card'
    ]);

    $invoice->refresh();
    
    expect($invoice)
        ->amount_paid->toBe(500.00)
        ->status->toBe($invoice->status); // Still not fully paid

    expect($invoice->payments)->toHaveCount(2);
});

test('payment reference and notes are optional', function () {
    $invoice = Invoice::factory()->create();

    $response = $this->post(route('payments.store', $invoice), [
        'amount' => 100,
        'payment_date' => now()->toDateString(),
        'method' => 'cash'
        // reference and notes are optional
    ]);

    $response->assertRedirect();
    
    $payment = Payment::latest()->first();
    expect($payment)
        ->reference->toBeNull()
        ->notes->toBeNull();
});

test('can record payment with all optional fields', function () {
    $invoice = Invoice::factory()->create();

    $response = $this->post(route('payments.store', $invoice), [
        'amount' => 250,
        'payment_date' => now()->toDateString(),
        'method' => 'paypal',
        'reference' => 'PAYPAL-TXN-123456',
        'notes' => 'Payment received via PayPal'
    ]);

    $payment = Payment::latest()->first();
    
    expect($payment)
        ->amount->toBe(250.00)
        ->method->toBe('paypal')
        ->reference->toBe('PAYPAL-TXN-123456')
        ->notes->toBe('Payment received via PayPal');

    $response->assertRedirect();
});

test('overpayment is allowed and tracked correctly', function () {
    $invoice = Invoice::factory()->create(['total' => 1000]);

    $this->post(route('payments.store', $invoice), [
        'amount' => 1200, // Overpayment
        'payment_date' => now()->toDateString(),
        'method' => 'bank_transfer'
    ]);

    $invoice->refresh();
    
    expect($invoice)
        ->status->toBe('paid')
        ->amount_paid->toBe(1200.00)
        ->paid_at->not->toBeNull();

    // Remaining balance should be negative (credit)
    expect($invoice->remaining_balance)->toBe(-200.00);
});
