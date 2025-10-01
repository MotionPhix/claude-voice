<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->organization = Organization::factory()->create();

    $this->owner = User::factory()->create();
    $this->manager = User::factory()->create();
    $this->user = User::factory()->create();

    Membership::factory()->owner()->create([
        'user_id' => $this->owner->id,
        'organization_id' => $this->organization->id,
    ]);

    Membership::factory()->manager()->create([
        'user_id' => $this->manager->id,
        'organization_id' => $this->organization->id,
    ]);

    Membership::factory()->user()->create([
        'user_id' => $this->user->id,
        'organization_id' => $this->organization->id,
    ]);

    set_current_organization($this->organization);
});

describe('Invoice Authorization', function () {
    it('allows manager to create invoice', function () {
        $this->actingAs($this->manager);

        $client = Client::factory()->for($this->organization)->create();

        $response = $this->post(route('invoices.store'), [
            'client_id' => $client->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'tax_rate' => 15,
            'discount' => 0,
            'notes' => 'Test invoice',
            'terms' => 'Net 30',
            'items' => [
                [
                    'description' => 'Test Service',
                    'quantity' => 1,
                    'unit_price' => 1000,
                ],
            ],
        ]);

        $response->assertRedirect();
        expect(Invoice::count())->toBe(1);
    });

    it('prevents user role from creating invoice', function () {
        $this->actingAs($this->user);

        $client = Client::factory()->for($this->organization)->create();

        $response = $this->post(route('invoices.store'), [
            'client_id' => $client->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                [
                    'description' => 'Test Service',
                    'quantity' => 1,
                    'unit_price' => 1000,
                ],
            ],
        ]);

        $response->assertForbidden();
        expect(Invoice::count())->toBe(0);
    });

    it('allows manager to edit draft invoice', function () {
        $this->actingAs($this->manager);

        $client = Client::factory()->for($this->organization)->create();
        $invoice = Invoice::factory()
            ->draft()
            ->for($this->organization)
            ->for($client, 'client')
            ->create();

        $response = $this->get(route('invoices.edit', $invoice));

        $response->assertSuccessful();
    });

    it('prevents editing sent invoice', function () {
        $this->actingAs($this->manager);

        $client = Client::factory()->for($this->organization)->create();
        $invoice = Invoice::factory()
            ->sent()
            ->for($this->organization)
            ->for($client, 'client')
            ->create();

        $response = $this->get(route('invoices.edit', $invoice));

        $response->assertRedirect(route('invoices.show', $invoice));
        $response->assertSessionHas('error');
    });

    it('prevents user from deleting draft invoice', function () {
        $this->actingAs($this->user);

        $client = Client::factory()->for($this->organization)->create();
        $invoice = Invoice::factory()
            ->draft()
            ->for($this->organization)
            ->for($client, 'client')
            ->create();

        $response = $this->delete(route('invoices.destroy', $invoice));

        $response->assertForbidden();
        expect(Invoice::find($invoice->id))->not->toBeNull();
    });

    it('allows manager to send draft invoice', function () {
        $this->actingAs($this->manager);

        $client = Client::factory()->for($this->organization)->create();
        $invoice = Invoice::factory()
            ->draft()
            ->for($this->organization)
            ->for($client, 'client')
            ->create();

        $response = $this->post(route('invoices.send', $invoice));

        $response->assertRedirect();
    });

    it('prevents user from sending invoice', function () {
        $this->actingAs($this->user);

        $client = Client::factory()->for($this->organization)->create();
        $invoice = Invoice::factory()
            ->draft()
            ->for($this->organization)
            ->for($client, 'client')
            ->create();

        $response = $this->post(route('invoices.send', $invoice));

        $response->assertForbidden();
    });
});

describe('Client Authorization', function () {
    it('allows manager to create client', function () {
        $this->actingAs($this->manager);

        $response = $this->post(route('clients.store'), [
            'name' => 'Test Client',
            'email' => 'client@test.com',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        expect(Client::count())->toBe(1);
    });

    it('prevents user from creating client', function () {
        $this->actingAs($this->user);

        $response = $this->post(route('clients.store'), [
            'name' => 'Test Client',
            'email' => 'client@test.com',
            'is_active' => true,
        ]);

        $response->assertForbidden();
        expect(Client::count())->toBe(0);
    });

    it('allows manager to update client', function () {
        $this->actingAs($this->manager);

        $client = Client::factory()->for($this->organization)->create();

        $response = $this->put(route('clients.update', $client), [
            'name' => 'Updated Client',
            'email' => $client->email,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        expect($client->fresh()->name)->toBe('Updated Client');
    });

    it('prevents user from updating client', function () {
        $this->actingAs($this->user);

        $client = Client::factory()->for($this->organization)->create();
        $originalName = $client->name;

        $response = $this->put(route('clients.update', $client), [
            'name' => 'Updated Client',
            'email' => $client->email,
            'is_active' => true,
        ]);

        $response->assertForbidden();
        expect($client->fresh()->name)->toBe($originalName);
    });

    it('prevents manager from deleting client', function () {
        $this->actingAs($this->manager);

        $client = Client::factory()->for($this->organization)->create();

        $response = $this->delete(route('clients.destroy', $client));

        $response->assertForbidden();
        expect(Client::find($client->id))->not->toBeNull();
    });

    it('allows owner to delete client', function () {
        $this->actingAs($this->owner);

        $client = Client::factory()->for($this->organization)->create();

        $response = $this->delete(route('clients.destroy', $client));

        $response->assertRedirect();
        expect(Client::find($client->id))->toBeNull();
    });
});

describe('Cross-Organization Authorization', function () {
    it('prevents accessing invoices from another organization', function () {
        $otherOrg = Organization::factory()->create();
        $otherClient = Client::factory()->for($otherOrg)->create();
        $otherInvoice = Invoice::factory()
            ->for($otherOrg)
            ->for($otherClient, 'client')
            ->create();

        $this->actingAs($this->owner);
        set_current_organization($this->organization);

        $response = $this->get(route('invoices.show', $otherInvoice));

        $response->assertNotFound();
    });

    it('prevents updating clients from another organization', function () {
        $otherOrg = Organization::factory()->create();
        $otherClient = Client::factory()->for($otherOrg)->create();

        $this->actingAs($this->owner);
        set_current_organization($this->organization);

        $response = $this->put(route('clients.update', $otherClient), [
            'name' => 'Hacked Name',
            'email' => $otherClient->email,
            'is_active' => true,
        ]);

        $response->assertNotFound();
    });

    it('prevents deleting invoices from another organization', function () {
        $otherOrg = Organization::factory()->create();
        $otherClient = Client::factory()->for($otherOrg)->create();
        $otherInvoice = Invoice::factory()
            ->for($otherOrg)
            ->for($otherClient, 'client')
            ->create();

        $this->actingAs($this->owner);
        set_current_organization($this->organization);

        $response = $this->delete(route('invoices.destroy', $otherInvoice));

        $response->assertNotFound();
        expect(Invoice::allOrganizations()->find($otherInvoice->id))->not->toBeNull();
    });
});

describe('View-Only Authorization', function () {
    it('allows all roles to view invoices', function () {
        $client = Client::factory()->for($this->organization)->create();
        $invoice = Invoice::factory()
            ->for($this->organization)
            ->for($client, 'client')
            ->create();

        $this->actingAs($this->user);

        $response = $this->get(route('invoices.show', $invoice));

        $response->assertSuccessful();
    });

    it('allows all roles to view clients', function () {
        $client = Client::factory()->for($this->organization)->create();

        $this->actingAs($this->user);

        $response = $this->get(route('clients.show', $client));

        $response->assertSuccessful();
    });

    it('allows all roles to download invoice PDF', function () {
        $client = Client::factory()->for($this->organization)->create();
        $invoice = Invoice::factory()
            ->for($this->organization)
            ->for($client, 'client')
            ->create();

        $this->actingAs($this->user);

        $response = $this->get(route('invoices.pdf', $invoice));

        $response->assertSuccessful();
    });
});
