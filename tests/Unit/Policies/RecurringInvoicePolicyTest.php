<?php

use App\Models\Membership;
use App\Models\Organization;
use App\Models\RecurringInvoice;
use App\Models\User;
use App\Policies\RecurringInvoicePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new RecurringInvoicePolicy;
    $this->organization = Organization::factory()->create();
    set_current_organization($this->organization);
});

describe('viewAny', function () {
    it('allows all roles to view recurring invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();

        expect($this->policy->viewAny($user))->toBeTrue();
    })->with(['owner', 'admin', 'manager', 'accountant', 'user']);
});

describe('view', function () {
    it('allows all roles to view recurring invoices in their organization', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $recurringInvoice = RecurringInvoice::factory()->for($this->organization)->create();

        expect($this->policy->view($user, $recurringInvoice))->toBeTrue();
    })->with(['owner', 'admin', 'manager', 'accountant', 'user']);

    it('denies viewing recurring invoices from other organizations', function () {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->owner()->create();

        $otherOrg = Organization::factory()->create();
        $recurringInvoice = RecurringInvoice::factory()->for($otherOrg)->create();

        expect($this->policy->view($user, $recurringInvoice))->toBeFalse();
    });
});

describe('create', function () {
    it('allows owners, admins, and managers to create recurring invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();

        expect($this->policy->create($user))->toBeTrue();
    })->with(['owner', 'admin', 'manager']);

    it('denies accountants and users from creating recurring invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();

        expect($this->policy->create($user))->toBeFalse();
    })->with(['accountant', 'user']);
});

describe('update', function () {
    it('allows owners, admins, and managers to update recurring invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $recurringInvoice = RecurringInvoice::factory()->for($this->organization)->create();

        expect($this->policy->update($user, $recurringInvoice))->toBeTrue();
    })->with(['owner', 'admin', 'manager']);

    it('denies accountants and users from updating recurring invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $recurringInvoice = RecurringInvoice::factory()->for($this->organization)->create();

        expect($this->policy->update($user, $recurringInvoice))->toBeFalse();
    })->with(['accountant', 'user']);
});

describe('delete', function () {
    it('allows owners and admins to delete recurring invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $recurringInvoice = RecurringInvoice::factory()->for($this->organization)->create();

        expect($this->policy->delete($user, $recurringInvoice))->toBeTrue();
    })->with(['owner', 'admin']);

    it('denies managers, accountants, and users from deleting recurring invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $recurringInvoice = RecurringInvoice::factory()->for($this->organization)->create();

        expect($this->policy->delete($user, $recurringInvoice))->toBeFalse();
    })->with(['manager', 'accountant', 'user']);
});

describe('toggleStatus', function () {
    it('allows owners, admins, and managers to toggle status', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $recurringInvoice = RecurringInvoice::factory()->for($this->organization)->create();

        expect($this->policy->toggleStatus($user, $recurringInvoice))->toBeTrue();
    })->with(['owner', 'admin', 'manager']);

    it('denies accountants and users from toggling status', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $recurringInvoice = RecurringInvoice::factory()->for($this->organization)->create();

        expect($this->policy->toggleStatus($user, $recurringInvoice))->toBeFalse();
    })->with(['accountant', 'user']);
});

describe('generateInvoice', function () {
    it('allows owners, admins, and managers to generate invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $recurringInvoice = RecurringInvoice::factory()->for($this->organization)->create();

        expect($this->policy->generateInvoice($user, $recurringInvoice))->toBeTrue();
    })->with(['owner', 'admin', 'manager']);

    it('denies accountants and users from generating invoices', function (string $role) {
        $user = User::factory()->create();
        Membership::factory()->for($user)->for($this->organization)->{$role}()->create();
        $recurringInvoice = RecurringInvoice::factory()->for($this->organization)->create();

        expect($this->policy->generateInvoice($user, $recurringInvoice))->toBeFalse();
    })->with(['accountant', 'user']);
});
