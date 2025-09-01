<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can get invoices via api', function () {
    $client = Client::factory()->create();
    Invoice::factory(3)->for($client)->create();

    $response = $this->getJson('/api/invoices');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'invoice_number',
                    'client',
                    'issue_date',
                    'due_date',
                    'status',
                    'total',
                    'currency'
                ]
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total'
            ]
        ])
        ->assertJson(['success' => true]);

    expect($response['data'])->toHaveCount(3);
});

test('can filter invoices by status via api', function () {
    $client = Client::factory()->create();
    Invoice::factory()->draft()->for($client)->create();
    Invoice::factory()->sent()->for($client)->create();
    $paidInvoice = Invoice::factory()->paid()->for($client)->create();

    $response = $this->getJson('/api/invoices?status=paid');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $paidInvoice->id);
});

test('can search invoices by invoice number via api', function () {
    $client = Client::factory()->create();
    $searchableInvoice = Invoice::factory()->for($client)->create([
        'invoice_number' => 'INV-2024-TEST'
    ]);
    Invoice::factory()->for($client)->create(['invoice_number' => 'INV-2024-OTHER']);

    $response = $this->getJson('/api/invoices?search=TEST');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $searchableInvoice->id);
});

test('can create invoice via api', function () {
    $client = Client::factory()->create();

    $invoiceData = [
        'client_id' => $client->id,
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'currency' => 'USD',
        'tax_rate' => 10,
        'discount' => 0,
        'notes' => 'API test invoice',
        'terms' => 'Net 30',
        'items' => [
            [
                'description' => 'API Testing Service',
                'quantity' => 1,
                'unit_price' => 500
            ]
        ]
    ];

    $response = $this->postJson('/api/invoices', $invoiceData);

    $response->assertCreated()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'invoice_number',
                'client',
                'items',
                'total'
            ]
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Invoice created successfully.'
        ]);

    $invoice = Invoice::latest()->first();
    expect($invoice)
        ->client_id->toBe($client->id)
        ->total->toBe(550.00) // 500 + (500 * 0.10)
        ->and($invoice->items)->toHaveCount(1);
});

test('api validates required fields when creating invoice', function () {
    $response = $this->postJson('/api/invoices', [
        'client_id' => 999, // Non-existent client
        'items' => [] // Empty items array
    ]);

    $response->assertUnprocessable()
        ->assertJsonStructure([
            'success',
            'message',
            'errors'
        ])
        ->assertJson(['success' => false]);
});

test('can get single invoice via api', function () {
    $client = Client::factory()->create();
    $invoice = Invoice::factory()->for($client)->create();

    $response = $this->getJson("/api/invoices/{$invoice->id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'invoice_number',
                'client',
                'items',
                'payments',
                'total'
            ]
        ])
        ->assertJsonPath('data.id', $invoice->id);
});

test('can update draft invoice via api', function () {
    $client = Client::factory()->create();
    $newClient = Client::factory()->create();
    $invoice = Invoice::factory()->draft()->for($client)->create();

    $updateData = [
        'client_id' => $newClient->id,
        'issue_date' => $invoice->issue_date->toDateString(),
        'due_date' => $invoice->due_date->toDateString(),
        'tax_rate' => 15,
        'items' => [
            [
                'description' => 'Updated API Service',
                'quantity' => 2,
                'unit_price' => 250
            ]
        ]
    ];

    $response = $this->putJson("/api/invoices/{$invoice->id}", $updateData);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Invoice updated successfully.'
        ]);

    $invoice->refresh();
    expect($invoice)
        ->client_id->toBe($newClient->id)
        ->tax_rate->toBe(15.00);
});

test('cannot update sent invoice via api', function () {
    $invoice = Invoice::factory()->sent()->create();

    $updateData = [
        'client_id' => $invoice->client_id,
        'issue_date' => $invoice->issue_date->toDateString(),
        'due_date' => $invoice->due_date->toDateString(),
        'items' => [
            [
                'description' => 'Updated Service',
                'quantity' => 1,
                'unit_price' => 100
            ]
        ]
    ];

    $response = $this->putJson("/api/invoices/{$invoice->id}", $updateData);

    $response->assertBadRequest()
        ->assertJson([
            'success' => false,
            'message' => 'Only draft invoices can be updated.'
        ]);
});

test('can mark invoice as paid via api', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 1000]);

    $paymentData = [
        'amount' => 1000,
        'payment_date' => now()->toDateString(),
        'method' => 'bank_transfer',
        'reference' => 'API-TEST-TXN',
        'notes' => 'Full payment via API'
    ];

    $response = $this->postJson("/api/invoices/{$invoice->id}/mark-paid", $paymentData);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Invoice marked as paid successfully.'
        ]);

    $invoice->refresh();
    expect($invoice->status)->toBe('paid');
    expect($invoice->payments)->toHaveCount(1);
});

test('cannot mark already paid invoice as paid via api', function () {
    $invoice = Invoice::factory()->paid()->create();

    $response = $this->postJson("/api/invoices/{$invoice->id}/mark-paid", [
        'amount' => $invoice->total,
        'payment_date' => now()->toDateString(),
        'method' => 'cash'
    ]);

    $response->assertBadRequest()
        ->assertJson([
            'success' => false,
            'message' => 'Invoice is already paid.'
        ]);
});

test('can get invoice statistics via api', function () {
    $client = Client::factory()->create();
    
    Invoice::factory(3)->draft()->for($client)->create(['total' => 1000]);
    Invoice::factory(2)->paid()->for($client)->create(['total' => 1500]);
    Invoice::factory(1)->overdue()->for($client)->create(['total' => 2000]);

    $response = $this->getJson('/api/invoices/stats');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'total_count',
                'total_amount',
                'paid_amount',
                'pending_amount',
                'overdue_count',
                'draft_count',
                'by_currency'
            ]
        ])
        ->assertJson(['success' => true]);

    expect($response['data']['total_count'])->toBe(6);
    expect($response['data']['draft_count'])->toBe(3);
    expect($response['data']['overdue_count'])->toBe(1);
});

test('can filter statistics by date range via api', function () {
    $client = Client::factory()->create();
    
    // Old invoice (should be excluded)
    Invoice::factory()->create([
        'client_id' => $client->id,
        'issue_date' => now()->subMonths(2),
        'total' => 500
    ]);
    
    // Recent invoice (should be included)
    Invoice::factory()->create([
        'client_id' => $client->id,
        'issue_date' => now()->subDays(5),
        'total' => 1000
    ]);

    $response = $this->getJson('/api/invoices/stats?' . http_build_query([
        'date_from' => now()->subDays(7)->toDateString(),
        'date_to' => now()->toDateString()
    ]));

    $response->assertSuccessful();
    
    expect($response['data']['total_count'])->toBe(1);
    expect($response['data']['total_amount'])->toBe(1000.00);
});

test('api pagination works correctly', function () {
    $client = Client::factory()->create();
    Invoice::factory(25)->for($client)->create();

    $response = $this->getJson('/api/invoices?per_page=10&page=1');

    $response->assertSuccessful()
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.total', 25)
        ->assertJsonCount(10, 'data');

    // Test second page
    $response = $this->getJson('/api/invoices?per_page=10&page=2');

    $response->assertSuccessful()
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonCount(10, 'data');
});

test('api respects per page limit', function () {
    $client = Client::factory()->create();
    Invoice::factory(150)->for($client)->create();

    // Test default limit
    $response = $this->getJson('/api/invoices');
    expect($response['data'])->toHaveCount(15); // Default per_page

    // Test custom limit
    $response = $this->getJson('/api/invoices?per_page=50');
    expect($response['data'])->toHaveCount(50);

    // Test exceeding maximum limit (should be capped at 100)
    $response = $this->getJson('/api/invoices?per_page=150');
    expect($response['data'])->toHaveCount(100); // Maximum per_page
});
