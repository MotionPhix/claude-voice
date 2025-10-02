<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// Add per-test auth setup
uses()->beforeEach(function () {
    // Ensure organization membership whenever an organization is created
    \App\Models\Organization::created(function ($organization) {
        if (auth()->check()) {
            \Database\Factories\MembershipFactory::new()->owner()->create([
                'user_id' => auth()->id(),
                'organization_id' => $organization->id,
            ]);
            session(['current_organization_id' => $organization->id]);
        }
    });

    // When Client is created, ensure it has an organization and auth user has membership
    Client::created(function ($client) {
        if (auth()->check()) {
            if (! $client->organization_id) {
                $org = \App\Models\Organization::factory()->create();
                $client->organization_id = $org->id;
                $client->saveQuietly();
            }

            // Membership is created by Organization created event
            session(['current_organization_id' => $client->organization_id]);
        }
    });

    // When Invoice is created, ensure organization membership exists
    Invoice::created(function ($invoice) {
        if (auth()->check()) {
            if (! $invoice->organization_id) {
                // Create org if none exists
                $org = \App\Models\Organization::factory()->create();
                $invoice->organization_id = $org->id;
                $invoice->saveQuietly();
            }

            // Membership is created by Organization created event
            session(['current_organization_id' => $invoice->organization_id]);
        }
    });
})->in('tests/Feature');

test('can view invoice index page', function () {
    $client = Client::factory()->create();
    $invoices = Invoice::factory(3)->for($client)->create();

    $response = $this->get(route('invoices.index'));

    $response->assertSuccessful()
        ->assertInertia(fn ($assert) => $assert
            ->component('invoices/Index')
            ->has('invoices.data', 3)
        );
});

test('can filter invoices by status', function () {
    $client = Client::factory()->create();
    Invoice::factory()->draft()->for($client)->create();
    Invoice::factory()->sent()->for($client)->create();
    $paidInvoice = Invoice::factory()->paid()->for($client)->create();

    $response = $this->get(route('invoices.index', ['status' => 'paid']));

    $response->assertSuccessful()
        ->assertInertia(fn ($assert) => $assert
            ->component('invoices/Index')
            ->has('invoices.data', 1)
            ->where('invoices.data.0.id', $paidInvoice->id)
        );
});

test('can search invoices by invoice number', function () {
    $client = Client::factory()->create();
    $searchableInvoice = Invoice::factory()->for($client)->create([
        'invoice_number' => 'INV-2024-0001'
    ]);
    Invoice::factory()->for($client)->create(['invoice_number' => 'INV-2024-0002']);

    $response = $this->get(route('invoices.index', ['search' => 'INV-2024-0001']));

    $response->assertSuccessful()
        ->assertInertia(fn ($assert) => $assert
            ->has('invoices.data', 1)
            ->where('invoices.data.0.id', $searchableInvoice->id)
        );
});

test('can search invoices by client name', function () {
    $client1 = Client::factory()->create(['name' => 'ABC Company']);
    $client2 = Client::factory()->create(['name' => 'XYZ Corporation']);

    $invoice1 = Invoice::factory()->for($client1)->create();
    Invoice::factory()->for($client2)->create();

    $response = $this->get(route('invoices.index', ['search' => 'ABC']));

    $response->assertSuccessful()
        ->assertInertia(fn ($assert) => $assert
            ->has('invoices.data', 1)
            ->where('invoices.data.0.id', $invoice1->id)
        );
});

test('can filter invoices by date range', function () {
    $client = Client::factory()->create();
    $oldInvoice = Invoice::factory()->for($client)->create([
        'issue_date' => now()->subMonths(2)
    ]);
    $recentInvoice = Invoice::factory()->for($client)->create([
        'issue_date' => now()->subDays(5)
    ]);

    $response = $this->get(route('invoices.index', [
        'date_from' => now()->subDays(7)->toDateString(),
        'date_to' => now()->toDateString()
    ]));

    $response->assertSuccessful()
        ->assertInertia(fn ($assert) => $assert
            ->has('invoices.data', 1)
            ->where('invoices.data.0.id', $recentInvoice->id)
        );
});

test('can view create invoice page', function () {
    Client::factory(3)->create();

    $response = $this->get(route('invoices.create'));

    $response->assertSuccessful()
        ->assertInertia(fn ($assert) => $assert
            ->component('invoices/Create')
            ->has('clients', 3)
        );
});

test('can create invoice with valid data', function () {
    $client = Client::factory()->create();

    $invoiceData = [
        'client_id' => $client->id,
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'tax_rate' => 10,
        'discount' => 50,
        'notes' => 'Test notes',
        'terms' => 'Net 30',
        'items' => [
            [
                'description' => 'Web Development',
                'quantity' => 10,
                'unit_price' => 100
            ],
            [
                'description' => 'Consulting',
                'quantity' => 5,
                'unit_price' => 150
            ]
        ]
    ];

    $response = $this->post(route('invoices.store'), $invoiceData);

    $invoice = Invoice::latest()->first();

    expect($invoice)
        ->client_id->toBe($client->id)
        ->tax_rate->toBe(10.00)
        ->discount->toBe(50.00)
        ->subtotal->toBe(1750.00) // (10 * 100) + (5 * 150)
        ->tax_amount->toBe(175.00) // 10% of 1750
        ->total->toBe(1875.00); // 1750 + 175 - 50

    expect($invoice->items)->toHaveCount(2);

    $response->assertRedirect(route('invoices.show', $invoice))
        ->assertSessionHas('success', 'Invoice created successfully.');
});

test('cannot create invoice with invalid data', function () {
    $response = $this->post(route('invoices.store'), [
        'client_id' => 999, // Non-existent client
        'issue_date' => 'invalid-date',
        'due_date' => '2023-01-01', // Before issue date
        'items' => [] // Empty items array
    ]);

    $response->assertSessionHasErrors([
        'client_id',
        'issue_date',
        'due_date',
        'items'
    ]);
});

test('can view invoice details', function () {
    $client = Client::factory()->create();
    $invoice = Invoice::factory()->for($client)->create();
    InvoiceItem::factory(2)->for($invoice)->create();

    $response = $this->get(route('invoices.show', $invoice));

    $response->assertSuccessful()
        ->assertInertia(fn ($assert) => $assert
            ->component('invoices/Show')
            ->has('invoice.client')
            ->has('invoice.items', 2)
        );
});

test('can edit draft invoice', function () {
    $client = Client::factory()->create();
    $invoice = Invoice::factory()->draft()->for($client)->create();

    $response = $this->get(route('invoices.edit', $invoice));

    $response->assertSuccessful()
        ->assertInertia(fn ($assert) => $assert
            ->component('invoices/Edit')
            ->where('invoice.id', $invoice->id)
            ->has('clients')
        );
});

test('cannot edit sent invoice', function () {
    $invoice = Invoice::factory()->sent()->create();

    $response = $this->get(route('invoices.edit', $invoice));

    $response->assertRedirect(route('invoices.show', $invoice))
        ->assertSessionHas('error', 'Only draft invoices can be edited.');
});

test('can update draft invoice', function () {
    $client = Client::factory()->create();
    $newClient = Client::factory()->create();
    $invoice = Invoice::factory()->draft()->for($client)->create();
    InvoiceItem::factory(2)->for($invoice)->create();

    $updateData = [
        'client_id' => $newClient->id,
        'issue_date' => now()->addDays(1)->toDateString(),
        'due_date' => now()->addDays(31)->toDateString(),
        'tax_rate' => 15,
        'items' => [
            [
                'description' => 'Updated service',
                'quantity' => 1,
                'unit_price' => 500
            ]
        ]
    ];

    $response = $this->put(route('invoices.update', $invoice), $updateData);

    $invoice->refresh();

    expect($invoice)
        ->client_id->toBe($newClient->id)
        ->tax_rate->toBe(15.00)
        ->total->toBe(575.00); // 500 + (500 * 0.15)

    expect($invoice->items)->toHaveCount(1);

    $response->assertRedirect(route('invoices.show', $invoice))
        ->assertSessionHas('success', 'Invoice updated successfully.');
});

test('cannot update sent invoice', function () {
    $invoice = Invoice::factory()->sent()->create();

    $response = $this->put(route('invoices.update', $invoice), [
        'client_id' => $invoice->client_id,
        'issue_date' => $invoice->issue_date->toDateString(),
        'due_date' => $invoice->due_date->toDateString(),
        'items' => [['description' => 'Test', 'quantity' => 1, 'unit_price' => 100]]
    ]);

    $response->assertForbidden();
});

test('can delete draft invoice', function () {
    $invoice = Invoice::factory()->draft()->create();

    $response = $this->delete(route('invoices.destroy', $invoice));

    expect(Invoice::find($invoice->id))->toBeNull();

    $response->assertRedirect(route('invoices.index'))
        ->assertSessionHas('success', 'Invoice deleted successfully.');
});

test('cannot delete paid invoice', function () {
    $invoice = Invoice::factory()->paid()->create();

    $response = $this->delete(route('invoices.destroy', $invoice));

    expect(Invoice::find($invoice->id))->not->toBeNull();

    $response->assertRedirect(route('invoices.index'))
        ->assertSessionHas('error', 'Paid invoices cannot be deleted.');
});

test('can send draft invoice', function () {
    $invoice = Invoice::factory()->draft()->create();

    $response = $this->post(route('invoices.send', $invoice));

    $invoice->refresh();

    expect($invoice->status)->toBe('sent');
    expect($invoice->sent_at)->not->toBeNull();

    $response->assertRedirect(route('invoices.show', $invoice));
});

test('cannot send already sent invoice', function () {
    $invoice = Invoice::factory()->sent()->create();

    $response = $this->post(route('invoices.send', $invoice));

    $response->assertRedirect(route('invoices.show', $invoice))
        ->assertSessionHas('error', 'Only draft invoices can be sent.');
});

test('can generate PDF for invoice', function () {
    $invoice = Invoice::factory()->create();

    $response = $this->get(route('invoices.pdf', $invoice));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

test('can duplicate invoice', function () {
    $invoice = Invoice::factory()->create();
    InvoiceItem::factory(2)->for($invoice)->create();

    $response = $this->post(route('invoices.duplicate', $invoice));

    $duplicatedInvoice = Invoice::latest()->first();

    expect($duplicatedInvoice)
        ->not->toBe($invoice)
        ->client_id->toBe($invoice->client_id)
        ->status->toBe('draft')
        ->invoice_number->not->toBe($invoice->invoice_number);

    expect($duplicatedInvoice->items)->toHaveCount(2);

    $response->assertRedirect(route('invoices.edit', $duplicatedInvoice));
});
