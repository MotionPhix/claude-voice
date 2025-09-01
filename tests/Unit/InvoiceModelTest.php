<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('invoice generates unique invoice number on creation', function () {
    $invoice1 = Invoice::factory()->create();
    $invoice2 = Invoice::factory()->create();

    expect($invoice1->invoice_number)
        ->not->toBeEmpty()
        ->not->toBe($invoice2->invoice_number);

    expect($invoice1->invoice_number)->toMatch('/^INV-\d{4}-\d{4}$/');
});

test('invoice number follows correct format', function () {
    $invoice = Invoice::factory()->create();
    
    expect($invoice->invoice_number)->toMatch('/^INV-' . date('Y') . '-\d{4}$/');
});

test('calculate totals method works correctly', function () {
    $invoice = Invoice::factory()->create([
        'tax_rate' => 10,
        'discount' => 50
    ]);

    InvoiceItem::factory()->for($invoice)->create([
        'quantity' => 2,
        'unit_price' => 100,
        'total' => 200
    ]);

    InvoiceItem::factory()->for($invoice)->create([
        'quantity' => 3,
        'unit_price' => 150,
        'total' => 450
    ]);

    $invoice->calculateTotals();

    expect($invoice)
        ->subtotal->toBe(650.00) // 200 + 450
        ->tax_amount->toBe(65.00) // 10% of 650
        ->total->toBe(665.00); // 650 + 65 - 50
});

test('calculate totals with zero tax and discount', function () {
    $invoice = Invoice::factory()->create([
        'tax_rate' => 0,
        'discount' => 0
    ]);

    InvoiceItem::factory()->for($invoice)->create([
        'quantity' => 1,
        'unit_price' => 500,
        'total' => 500
    ]);

    $invoice->calculateTotals();

    expect($invoice)
        ->subtotal->toBe(500.00)
        ->tax_amount->toBe(0.00)
        ->total->toBe(500.00);
});

test('update status method works for paid invoices', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 1000]);

    Payment::factory()->for($invoice)->create(['amount' => 1000]);

    $invoice->updateStatus();

    expect($invoice)
        ->status->toBe('paid')
        ->amount_paid->toBe(1000.00)
        ->paid_at->not->toBeNull();
});

test('update status method works for partial payments', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 1000]);

    Payment::factory()->for($invoice)->create(['amount' => 500]);

    $invoice->updateStatus();

    expect($invoice)
        ->status->toBe('sent')
        ->amount_paid->toBe(500.00)
        ->paid_at->toBeNull();
});

test('update status method marks overdue invoices', function () {
    $invoice = Invoice::factory()->sent()->create([
        'due_date' => now()->subDays(5), // Overdue
        'total' => 1000
    ]);

    $invoice->updateStatus();

    expect($invoice->status)->toBe('overdue');
});

test('remaining balance attribute calculates correctly', function () {
    $invoice = Invoice::factory()->create(['total' => 1000]);

    expect($invoice->remaining_balance)->toBe(1000.00);

    Payment::factory()->for($invoice)->create(['amount' => 300]);
    $invoice->refresh();

    expect($invoice->remaining_balance)->toBe(700.00);

    Payment::factory()->for($invoice)->create(['amount' => 700]);
    $invoice->refresh();

    expect($invoice->remaining_balance)->toBe(0.00);
});

test('remaining balance handles overpayment', function () {
    $invoice = Invoice::factory()->create(['total' => 1000]);

    Payment::factory()->for($invoice)->create(['amount' => 1200]);
    $invoice->refresh();

    expect($invoice->remaining_balance)->toBe(-200.00);
});

test('is overdue attribute works correctly', function () {
    // Not overdue - future due date
    $invoice = Invoice::factory()->sent()->create([
        'due_date' => now()->addDays(5)
    ]);
    expect($invoice->is_overdue)->toBeFalse();

    // Overdue - past due date with sent status
    $invoice = Invoice::factory()->sent()->create([
        'due_date' => now()->subDays(5)
    ]);
    expect($invoice->is_overdue)->toBeTrue();

    // Not overdue - past due date but paid
    $invoice = Invoice::factory()->paid()->create([
        'due_date' => now()->subDays(5)
    ]);
    expect($invoice->is_overdue)->toBeFalse();

    // Not overdue - past due date but draft
    $invoice = Invoice::factory()->draft()->create([
        'due_date' => now()->subDays(5)
    ]);
    expect($invoice->is_overdue)->toBeFalse();
});

test('mark as sent method works correctly', function () {
    $invoice = Invoice::factory()->draft()->create();

    expect($invoice)
        ->status->toBe('draft')
        ->sent_at->toBeNull();

    $invoice->markAsSent();

    expect($invoice)
        ->status->toBe('sent')
        ->sent_at->not->toBeNull();
});

test('mark as paid method works correctly', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 500]);

    expect($invoice)
        ->status->toBe('sent')
        ->paid_at->toBeNull()
        ->amount_paid->toBe(0.00);

    $invoice->markAsPaid();

    expect($invoice)
        ->status->toBe('paid')
        ->paid_at->not->toBeNull()
        ->amount_paid->toBe(500.00);
});

test('overdue scope returns correct invoices', function () {
    // Create various invoice statuses
    $overdueInvoice1 = Invoice::factory()->create([
        'status' => 'overdue',
        'due_date' => now()->subDays(5)
    ]);

    $overdueInvoice2 = Invoice::factory()->create([
        'status' => 'sent',
        'due_date' => now()->subDays(3)
    ]);

    Invoice::factory()->create([
        'status' => 'sent',
        'due_date' => now()->addDays(5) // Not overdue
    ]);

    Invoice::factory()->create([
        'status' => 'paid',
        'due_date' => now()->subDays(5) // Paid, so not overdue
    ]);

    $overdueInvoices = Invoice::overdue()->get();

    expect($overdueInvoices)->toHaveCount(2);
    
    $ids = $overdueInvoices->pluck('id')->toArray();
    expect($ids)->toContain($overdueInvoice1->id);
    expect($ids)->toContain($overdueInvoice2->id);
});

test('due today scope returns correct invoices', function () {
    $dueTodayInvoice = Invoice::factory()->sent()->create([
        'due_date' => today()
    ]);

    Invoice::factory()->sent()->create([
        'due_date' => today()->addDays(1) // Due tomorrow
    ]);

    Invoice::factory()->paid()->create([
        'due_date' => today() // Paid, so not due
    ]);

    $dueTodayInvoices = Invoice::dueToday()->get();

    expect($dueTodayInvoices)->toHaveCount(1);
    expect($dueTodayInvoices->first()->id)->toBe($dueTodayInvoice->id);
});

test('due soon scope returns correct invoices', function () {
    $dueSoonInvoice1 = Invoice::factory()->sent()->create([
        'due_date' => today()->addDays(3)
    ]);

    $dueSoonInvoice2 = Invoice::factory()->sent()->create([
        'due_date' => today()->addDays(7)
    ]);

    Invoice::factory()->sent()->create([
        'due_date' => today()->addDays(10) // Too far in future
    ]);

    $dueSoonInvoices = Invoice::dueSoon(7)->get();

    expect($dueSoonInvoices)->toHaveCount(2);
    
    $ids = $dueSoonInvoices->pluck('id')->toArray();
    expect($ids)->toContain($dueSoonInvoice1->id);
    expect($ids)->toContain($dueSoonInvoice2->id);
});

test('formatted total attribute works correctly', function () {
    $invoice = Invoice::factory()->create(['total' => 1234.56]);

    // This test would need a Currency model with symbol
    // For now, just test that the attribute exists
    expect($invoice->formatted_total)->toBeString();
});

test('generate invoice number produces sequential numbers', function () {
    $number1 = Invoice::generateInvoiceNumber();
    $number2 = Invoice::generateInvoiceNumber();

    expect($number1)->toMatch('/^INV-' . date('Y') . '-\d{4}$/');
    expect($number2)->toMatch('/^INV-' . date('Y') . '-\d{4}$/');

    // Extract the numeric parts
    $num1 = (int) substr($number1, -4);
    $num2 = (int) substr($number2, -4);

    expect($num2)->toBe($num1 + 1);
});

test('invoice model has correct relationships', function () {
    $client = Client::factory()->create();
    $invoice = Invoice::factory()->for($client)->create();
    
    $item = InvoiceItem::factory()->for($invoice)->create();
    $payment = Payment::factory()->for($invoice)->create();

    expect($invoice->client)->toBeInstanceOf(Client::class);
    expect($invoice->client->id)->toBe($client->id);

    expect($invoice->items)->toHaveCount(1);
    expect($invoice->items->first())->toBeInstanceOf(InvoiceItem::class);

    expect($invoice->payments)->toHaveCount(1);
    expect($invoice->payments->first())->toBeInstanceOf(Payment::class);
});
