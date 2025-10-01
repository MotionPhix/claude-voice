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
    set_current_organization($this->organization);
    $this->client = Client::factory()->for($this->organization)->create();
});

describe('invoice index authorization', function () {
    it('allows authorized roles to view invoice index', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();

        $response = $this->actingAs($user)->get(route('invoices.index'));

        $response->assertSuccessful();
    })->with(['owner', 'admin', 'manager', 'accountant', 'user']);

    it('denies users without membership from viewing invoice index', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('invoices.index'));

        $response->assertForbidden();
    });
});

describe('invoice create authorization', function () {
    it('allows owners, admins, and managers to view create form', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();

        $response = $this->actingAs($user)->get(route('invoices.create'));

        $response->assertSuccessful();
    })->with(['owner', 'admin', 'manager']);

    it('denies accountants and users from viewing create form', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();

        $response = $this->actingAs($user)->get(route('invoices.create'));

        $response->assertForbidden();
    })->with(['accountant', 'user']);

    it('allows authorized roles to store invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();

        $response = $this->actingAs($user)->post(route('invoices.store'), [
            'client_id' => $this->client->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                ['description' => 'Test Item', 'quantity' => 1, 'unit_price' => 100],
            ],
        ]);

        $response->assertRedirect();
        expect(Invoice::count())->toBe(1);
    })->with(['owner', 'admin', 'manager']);
});

describe('invoice view authorization', function () {
    it('allows all roles to view invoices in their organization', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $invoice = Invoice::factory()->for($this->organization)->create();

        $response = $this->actingAs($user)->get(route('invoices.show', $invoice));

        $response->assertSuccessful();
    })->with(['owner', 'admin', 'manager', 'accountant', 'user']);
});

describe('invoice update authorization', function () {
    it('allows authorized roles to update draft invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $invoice = Invoice::factory()->for($this->organization)->draft()->create();

        $response = $this->actingAs($user)->put(route('invoices.update', $invoice), [
            'client_id' => $invoice->client_id,
            'issue_date' => $invoice->issue_date->toDateString(),
            'due_date' => $invoice->due_date->toDateString(),
            'items' => [
                ['description' => 'Updated Item', 'quantity' => 1, 'unit_price' => 200],
            ],
        ]);

        $response->assertRedirect();
    })->with(['owner', 'admin', 'manager', 'accountant']);

    it('denies users from updating invoices', function () {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->user()->create();
        $invoice = Invoice::factory()->for($this->organization)->draft()->create();

        $response = $this->actingAs($user)->put(route('invoices.update', $invoice), [
            'client_id' => $invoice->client_id,
            'issue_date' => $invoice->issue_date->toDateString(),
            'due_date' => $invoice->due_date->toDateString(),
            'items' => [
                ['description' => 'Updated Item', 'quantity' => 1, 'unit_price' => 200],
            ],
        ]);

        $response->assertForbidden();
    });

    it('denies updating sent invoices even for authorized roles', function () {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->owner()->create();
        $invoice = Invoice::factory()->for($this->organization)->sent()->create();

        $response = $this->actingAs($user)->put(route('invoices.update', $invoice), [
            'client_id' => $invoice->client_id,
            'issue_date' => $invoice->issue_date->toDateString(),
            'due_date' => $invoice->due_date->toDateString(),
            'items' => [
                ['description' => 'Updated Item', 'quantity' => 1, 'unit_price' => 200],
            ],
        ]);

        $response->assertForbidden();
    });
});

describe('invoice delete authorization', function () {
    it('allows authorized roles to delete draft invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $invoice = Invoice::factory()->for($this->organization)->draft()->create();

        $response = $this->actingAs($user)->delete(route('invoices.destroy', $invoice));

        $response->assertRedirect();
        expect(Invoice::find($invoice->id))->toBeNull();
    })->with(['owner', 'admin', 'manager']);

    it('denies accountants and users from deleting invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $invoice = Invoice::factory()->for($this->organization)->draft()->create();

        $response = $this->actingAs($user)->delete(route('invoices.destroy', $invoice));

        $response->assertForbidden();
        expect(Invoice::find($invoice->id))->not->toBeNull();
    })->with(['accountant', 'user']);
});

describe('invoice send authorization', function () {
    it('allows authorized roles to send invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $invoice = Invoice::factory()->for($this->organization)->draft()->create();

        $response = $this->actingAs($user)->post(route('invoices.send', $invoice));

        $response->assertRedirect();
    })->with(['owner', 'admin', 'manager']);

    it('denies accountants and users from sending invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $invoice = Invoice::factory()->for($this->organization)->draft()->create();

        $response = $this->actingAs($user)->post(route('invoices.send', $invoice));

        $response->assertForbidden();
    })->with(['accountant', 'user']);
});

describe('invoice duplicate authorization', function () {
    it('allows authorized roles to duplicate invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $invoice = Invoice::factory()->for($this->organization)->create();

        $response = $this->actingAs($user)->post(route('invoices.duplicate', $invoice));

        $response->assertRedirect();
        expect(Invoice::count())->toBe(2);
    })->with(['owner', 'admin', 'manager']);

    it('denies accountants and users from duplicating invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $invoice = Invoice::factory()->for($this->organization)->create();

        $response = $this->actingAs($user)->post(route('invoices.duplicate', $invoice));

        $response->assertForbidden();
    })->with(['accountant', 'user']);
});

describe('invoice PDF download authorization', function () {
    it('allows all roles to download invoice PDF', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $invoice = Invoice::factory()->for($this->organization)->create();

        $response = $this->actingAs($user)->get(route('invoices.pdf', $invoice));

        $response->assertSuccessful();
    })->with(['owner', 'admin', 'manager', 'accountant', 'user']);
});
