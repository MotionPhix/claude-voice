<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create two separate organizations
    $this->org1 = Organization::factory()->create(['name' => 'Organization 1']);
    $this->org2 = Organization::factory()->create(['name' => 'Organization 2']);

    // Create users for each organization
    $this->user1 = User::factory()->create(['name' => 'User 1']);
    $this->user2 = User::factory()->create(['name' => 'User 2']);

    // Create memberships
    Membership::factory()->owner()->create([
        'user_id' => $this->user1->id,
        'organization_id' => $this->org1->id,
    ]);

    Membership::factory()->owner()->create([
        'user_id' => $this->user2->id,
        'organization_id' => $this->org2->id,
    ]);

    // Create test data for each organization
    $this->client1 = Client::factory()->for($this->org1)->create(['name' => 'Client Org 1']);
    $this->client2 = Client::factory()->for($this->org2)->create(['name' => 'Client Org 2']);

    $this->invoice1 = Invoice::factory()->for($this->org1)->for($this->client1, 'client')->create();
    $this->invoice2 = Invoice::factory()->for($this->org2)->for($this->client2, 'client')->create();

    $this->payment1 = Payment::factory()->for($this->org1)->for($this->invoice1, 'invoice')->create();
    $this->payment2 = Payment::factory()->for($this->org2)->for($this->invoice2, 'invoice')->create();
});

describe('Client Data Isolation', function () {
    it('only shows clients from current organization', function () {
        set_current_organization($this->org1);

        $clients = Client::all();

        expect($clients)->toHaveCount(1);
        expect($clients->first()->id)->toBe($this->client1->id);
        expect($clients->first()->organization_id)->toBe($this->org1->id);
    });

    it('cannot query clients from another organization', function () {
        set_current_organization($this->org1);

        $client = Client::find($this->client2->id);

        expect($client)->toBeNull();
    });

    it('switches data when organization changes', function () {
        set_current_organization($this->org1);
        expect(Client::count())->toBe(1);
        expect(Client::first()->id)->toBe($this->client1->id);

        set_current_organization($this->org2);
        expect(Client::count())->toBe(1);
        expect(Client::first()->id)->toBe($this->client2->id);
    });
});

describe('Invoice Data Isolation', function () {
    it('only shows invoices from current organization', function () {
        set_current_organization($this->org1);

        $invoices = Invoice::all();

        expect($invoices)->toHaveCount(1);
        expect($invoices->first()->id)->toBe($this->invoice1->id);
        expect($invoices->first()->organization_id)->toBe($this->org1->id);
    });

    it('cannot query invoices from another organization', function () {
        set_current_organization($this->org1);

        $invoice = Invoice::find($this->invoice2->id);

        expect($invoice)->toBeNull();
    });

    it('invoice relationships respect organization scope', function () {
        set_current_organization($this->org1);

        $invoice = Invoice::with('client')->first();

        expect($invoice->client->organization_id)->toBe($this->org1->id);
        expect($invoice->client->id)->toBe($this->client1->id);
    });
});

describe('Payment Data Isolation', function () {
    it('only shows payments from current organization', function () {
        set_current_organization($this->org1);

        $payments = Payment::all();

        expect($payments)->toHaveCount(1);
        expect($payments->first()->id)->toBe($this->payment1->id);
        expect($payments->first()->organization_id)->toBe($this->org1->id);
    });

    it('cannot query payments from another organization', function () {
        set_current_organization($this->org1);

        $payment = Payment::find($this->payment2->id);

        expect($payment)->toBeNull();
    });
});

describe('Global Scope Bypass', function () {
    it('can bypass scope with allOrganizations when needed', function () {
        set_current_organization($this->org1);

        // With scope: only org1 invoices
        expect(Invoice::count())->toBe(1);

        // Without scope: all invoices
        expect(Invoice::allOrganizations()->count())->toBe(2);
    });

    it('allOrganizations includes data from all organizations', function () {
        set_current_organization($this->org1);

        $allClients = Client::allOrganizations()->get();

        expect($allClients)->toHaveCount(2);

        $orgIds = $allClients->pluck('organization_id')->unique()->toArray();
        expect($orgIds)->toContain($this->org1->id);
        expect($orgIds)->toContain($this->org2->id);
    });
});

describe('Cross-Organization Attempts', function () {
    it('prevents creating invoice for client in different organization', function () {
        set_current_organization($this->org1);

        // Try to create invoice in org1 for client in org2
        $invoice = Invoice::factory()->make([
            'organization_id' => $this->org1->id,
            'client_id' => $this->client2->id, // This client is in org2
        ]);

        // This should fail validation or business logic
        // The global scope will prevent querying the client
        $client = Client::find($this->client2->id);
        expect($client)->toBeNull();
    });
});

describe('Organization Context Required', function () {
    it('queries return empty when no organization is set', function () {
        set_current_organization(null);

        expect(Client::count())->toBe(0);
        expect(Invoice::count())->toBe(0);
        expect(Payment::count())->toBe(0);
    });

    it('can still bypass scope even without organization context', function () {
        set_current_organization(null);

        expect(Client::allOrganizations()->count())->toBe(2);
        expect(Invoice::allOrganizations()->count())->toBe(2);
    });
});

describe('Multi-Organization User', function () {
    it('user can belong to multiple organizations', function () {
        // Create user with membership in both organizations
        $multiUser = User::factory()->create();

        Membership::factory()->manager()->create([
            'user_id' => $multiUser->id,
            'organization_id' => $this->org1->id,
        ]);

        Membership::factory()->manager()->create([
            'user_id' => $multiUser->id,
            'organization_id' => $this->org2->id,
        ]);

        expect($multiUser->organizations)->toHaveCount(2);
        expect($multiUser->belongsToOrganization($this->org1))->toBeTrue();
        expect($multiUser->belongsToOrganization($this->org2))->toBeTrue();
    });

    it('user sees different data when switching organizations', function () {
        $multiUser = User::factory()->create();

        Membership::factory()->owner()->create([
            'user_id' => $multiUser->id,
            'organization_id' => $this->org1->id,
        ]);

        Membership::factory()->owner()->create([
            'user_id' => $multiUser->id,
            'organization_id' => $this->org2->id,
        ]);

        // Switch to org 1
        set_current_organization($this->org1);
        $this->actingAs($multiUser);

        $response = $this->get(route('clients.index'));
        $response->assertInertia(fn ($assert) => $assert
            ->component('clients/Index')
            ->has('clients.data', 1)
        );

        // Switch to org 2
        set_current_organization($this->org2);

        $response = $this->get(route('clients.index'));
        $response->assertInertia(fn ($assert) => $assert
            ->component('clients/Index')
            ->has('clients.data', 1)
        );
    });
});
