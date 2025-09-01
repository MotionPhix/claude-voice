<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->client = Client::factory()->create([
        'name' => 'Test Client Company',
        'email' => 'client@test.com'
    ]);
});

test('can create invoice through browser', function () {
    visit('/login')
        ->assertSee('Login')
        ->type('email', $this->user->email)
        ->type('password', 'password')
        ->click('Log in')
        ->waitForUrl('/dashboard')
        ->assertSee('Dashboard');

    // Navigate to create invoice
    visit('/invoices/create')
        ->assertSee('Create Invoice')
        ->assertSee('Select Client')
        ->assertNoJavascriptErrors();

    // Fill out the invoice form
    $page = $this;
    $page->select('client_id', $this->client->id)
        ->type('issue_date', now()->format('Y-m-d'))
        ->type('due_date', now()->addDays(30)->format('Y-m-d'))
        ->type('tax_rate', '10')
        ->type('discount', '0')
        ->type('notes', 'This is a test invoice')
        ->type('terms', 'Net 30 days');

    // Add invoice items
    $page->type('items[0][description]', 'Web Development Services')
        ->type('items[0][quantity]', '10')
        ->type('items[0][unit_price]', '100');

    // Add second item
    $page->click('Add Item')
        ->type('items[1][description]', 'Consulting Services')
        ->type('items[1][quantity]', '5')
        ->type('items[1][unit_price]', '150');

    // Submit the form
    $page->click('Create Invoice')
        ->waitForUrl('/invoices/*')
        ->assertSee('Invoice created successfully')
        ->assertSee('Test Client Company')
        ->assertSee('Web Development Services')
        ->assertSee('Consulting Services')
        ->assertSee('$1,825.00'); // (10*100 + 5*150) * 1.1 = 1825
});

test('can edit draft invoice through browser', function () {
    $invoice = \App\Models\Invoice::factory()->draft()->create([
        'client_id' => $this->client->id,
    ]);

    \App\Models\InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'description' => 'Original Service',
        'quantity' => 1,
        'unit_price' => 500
    ]);

    visit('/login')
        ->type('email', $this->user->email)
        ->type('password', 'password')
        ->click('Log in');

    visit("/invoices/{$invoice->id}/edit")
        ->assertSee('Edit Invoice')
        ->assertSee($invoice->invoice_number)
        ->assertNoJavascriptErrors();

    // Update the invoice
    $page = $this;
    $page->clear('tax_rate')
        ->type('tax_rate', '15')
        ->clear('items[0][description]')
        ->type('items[0][description]', 'Updated Service Description')
        ->clear('items[0][unit_price]')
        ->type('items[0][unit_price]', '750');

    $page->click('Update Invoice')
        ->waitForUrl("/invoices/{$invoice->id}")
        ->assertSee('Invoice updated successfully')
        ->assertSee('Updated Service Description')
        ->assertSee('$862.50'); // 750 * 1.15 = 862.5
});

test('can send invoice through browser', function () {
    $invoice = \App\Models\Invoice::factory()->draft()->create([
        'client_id' => $this->client->id,
    ]);

    visit('/login')
        ->type('email', $this->user->email)
        ->type('password', 'password')
        ->click('Log in');

    visit("/invoices/{$invoice->id}")
        ->assertSee($invoice->invoice_number)
        ->assertSee('Draft')
        ->assertSee('Send Invoice');

    $page = $this;
    $page->click('Send Invoice')
        ->waitForReload()
        ->assertSee('Invoice sent successfully')
        ->assertSee('Sent')
        ->assertDontSee('Draft');
});

test('can record payment through browser', function () {
    $invoice = \App\Models\Invoice::factory()->sent()->create([
        'client_id' => $this->client->id,
        'total' => 1000
    ]);

    visit('/login')
        ->type('email', $this->user->email)
        ->type('password', 'password')
        ->click('Log in');

    visit("/invoices/{$invoice->id}")
        ->assertSee('Record Payment')
        ->click('Record Payment')
        ->assertSee('Record Payment');

    // Fill payment form
    $page = $this;
    $page->type('amount', '500')
        ->type('payment_date', now()->format('Y-m-d'))
        ->select('method', 'bank_transfer')
        ->type('reference', 'TXN-12345')
        ->type('notes', 'Partial payment received');

    $page->click('Record Payment')
        ->waitForReload()
        ->assertSee('Payment recorded successfully')
        ->assertSee('$500.00')
        ->assertSee('Remaining: $500.00');
});

test('can delete draft invoice through browser', function () {
    $invoice = \App\Models\Invoice::factory()->draft()->create([
        'client_id' => $this->client->id,
    ]);

    visit('/login')
        ->type('email', $this->user->email)
        ->type('password', 'password')
        ->click('Log in');

    visit("/invoices/{$invoice->id}")
        ->assertSee($invoice->invoice_number)
        ->click('More Options') // Dropdown menu
        ->click('Delete Invoice')
        ->assertSee('Are you sure?'); // Confirmation modal

    $page = $this;
    $page->click('Confirm Delete')
        ->waitForUrl('/invoices')
        ->assertSee('Invoice deleted successfully')
        ->assertDontSee($invoice->invoice_number);
});

test('can filter invoices through browser', function () {
    $draftInvoice = \App\Models\Invoice::factory()->draft()->create([
        'client_id' => $this->client->id,
        'invoice_number' => 'INV-2024-0001'
    ]);

    $sentInvoice = \App\Models\Invoice::factory()->sent()->create([
        'client_id' => $this->client->id,
        'invoice_number' => 'INV-2024-0002'
    ]);

    visit('/login')
        ->type('email', $this->user->email)
        ->type('password', 'password')
        ->click('Log in');

    visit('/invoices')
        ->assertSee('INV-2024-0001')
        ->assertSee('INV-2024-0002');

    // Filter by status
    $page = $this;
    $page->select('status', 'draft')
        ->click('Filter')
        ->waitForReload()
        ->assertSee('INV-2024-0001')
        ->assertDontSee('INV-2024-0002');

    // Clear filter and search
    $page->select('status', '')
        ->type('search', 'INV-2024-0002')
        ->click('Search')
        ->waitForReload()
        ->assertDontSee('INV-2024-0001')
        ->assertSee('INV-2024-0002');
});

test('can duplicate invoice through browser', function () {
    $invoice = \App\Models\Invoice::factory()->create([
        'client_id' => $this->client->id,
        'invoice_number' => 'INV-2024-0001'
    ]);

    \App\Models\InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'description' => 'Original Service',
    ]);

    visit('/login')
        ->type('email', $this->user->email)
        ->type('password', 'password')
        ->click('Log in');

    visit("/invoices/{$invoice->id}")
        ->click('More Options')
        ->click('Duplicate Invoice')
        ->waitForUrl('/invoices/*/edit')
        ->assertSee('Invoice duplicated successfully')
        ->assertSee('Original Service')
        ->assertDontSee('INV-2024-0001'); // Should have new number
});

test('invoice totals calculate correctly in browser', function () {
    visit('/login')
        ->type('email', $this->user->email)
        ->type('password', 'password')
        ->click('Log in');

    visit('/invoices/create')
        ->select('client_id', $this->client->id)
        ->type('issue_date', now()->format('Y-m-d'))
        ->type('due_date', now()->addDays(30)->format('Y-m-d'))
        ->type('tax_rate', '20')
        ->type('discount', '100');

    $page = $this;
    $page->type('items[0][description]', 'Test Service')
        ->type('items[0][quantity]', '2')
        ->type('items[0][unit_price]', '250')
        ->blur('items[0][unit_price]')  // Trigger calculation
        ->waitFor('.invoice-totals')
        ->assertSee('Subtotal: $500.00')  // 2 * 250
        ->assertSee('Tax (20%): $100.00')  // 500 * 0.20
        ->assertSee('Discount: $100.00')
        ->assertSee('Total: $500.00');  // 500 + 100 - 100
});

test('form validation works in browser', function () {
    visit('/login')
        ->type('email', $this->user->email)
        ->type('password', 'password')
        ->click('Log in');

    visit('/invoices/create')
        ->click('Create Invoice') // Submit without filling required fields
        ->assertSee('Please select a client')
        ->assertSee('Issue date is required')
        ->assertSee('Due date is required')
        ->assertSee('At least one invoice item is required');

    // Try with invalid due date
    $page = $this;
    $page->select('client_id', $this->client->id)
        ->type('issue_date', now()->format('Y-m-d'))
        ->type('due_date', now()->subDays(5)->format('Y-m-d')) // Before issue date
        ->type('items[0][description]', 'Test')
        ->type('items[0][quantity]', '1')
        ->type('items[0][unit_price]', '100')
        ->click('Create Invoice')
        ->assertSee('The due date must be on or after the issue date');
});
