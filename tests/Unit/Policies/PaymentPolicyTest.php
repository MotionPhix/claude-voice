<?php

use App\Models\Membership;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->organization = Organization::factory()->create();
    $this->otherOrganization = Organization::factory()->create();

    $this->owner = User::factory()->create();
    $this->admin = User::factory()->create();
    $this->manager = User::factory()->create();
    $this->accountant = User::factory()->create();
    $this->user = User::factory()->create();

    Membership::factory()->owner()->create([
        'user_id' => $this->owner->id,
        'organization_id' => $this->organization->id,
    ]);

    Membership::factory()->admin()->create([
        'user_id' => $this->admin->id,
        'organization_id' => $this->organization->id,
    ]);

    Membership::factory()->manager()->create([
        'user_id' => $this->manager->id,
        'organization_id' => $this->organization->id,
    ]);

    Membership::factory()->accountant()->create([
        'user_id' => $this->accountant->id,
        'organization_id' => $this->organization->id,
    ]);

    Membership::factory()->user()->create([
        'user_id' => $this->user->id,
        'organization_id' => $this->organization->id,
    ]);

    set_current_organization($this->organization);

    $this->payment = Payment::factory()->for($this->organization)->create();
});

describe('PaymentPolicy viewAny', function () {
    it('allows all roles to view payments', function () {
        expect($this->owner->can('viewAny', Payment::class))->toBeTrue();
        expect($this->admin->can('viewAny', Payment::class))->toBeTrue();
        expect($this->manager->can('viewAny', Payment::class))->toBeTrue();
        expect($this->accountant->can('viewAny', Payment::class))->toBeTrue();
        expect($this->user->can('viewAny', Payment::class))->toBeTrue();
    });
});

describe('PaymentPolicy view', function () {
    it('allows all roles to view payments in their organization', function () {
        expect($this->owner->can('view', $this->payment))->toBeTrue();
        expect($this->admin->can('view', $this->payment))->toBeTrue();
        expect($this->manager->can('view', $this->payment))->toBeTrue();
        expect($this->accountant->can('view', $this->payment))->toBeTrue();
        expect($this->user->can('view', $this->payment))->toBeTrue();
    });

    it('denies viewing payments from other organizations', function () {
        $otherPayment = Payment::factory()->for($this->otherOrganization)->create();

        expect($this->owner->can('view', $otherPayment))->toBeFalse();
    });
});

describe('PaymentPolicy create', function () {
    it('allows owner, admin, manager, and accountant to create payments', function () {
        expect($this->owner->can('create', Payment::class))->toBeTrue();
        expect($this->admin->can('create', Payment::class))->toBeTrue();
        expect($this->manager->can('create', Payment::class))->toBeTrue();
        expect($this->accountant->can('create', Payment::class))->toBeTrue();
    });

    it('denies user role from creating payments', function () {
        expect($this->user->can('create', Payment::class))->toBeFalse();
    });
});

describe('PaymentPolicy update', function () {
    it('allows owner, admin, and accountant to update payments', function () {
        expect($this->owner->can('update', $this->payment))->toBeTrue();
        expect($this->admin->can('update', $this->payment))->toBeTrue();
        expect($this->accountant->can('update', $this->payment))->toBeTrue();
    });

    it('denies manager and user from updating payments', function () {
        expect($this->manager->can('update', $this->payment))->toBeFalse();
        expect($this->user->can('update', $this->payment))->toBeFalse();
    });

    it('denies updating payments from other organizations', function () {
        $otherPayment = Payment::factory()->for($this->otherOrganization)->create();

        expect($this->owner->can('update', $otherPayment))->toBeFalse();
    });
});

describe('PaymentPolicy delete', function () {
    it('allows owner, admin, and accountant to delete payments', function () {
        expect($this->owner->can('delete', $this->payment))->toBeTrue();
        expect($this->admin->can('delete', $this->payment))->toBeTrue();
        expect($this->accountant->can('delete', $this->payment))->toBeTrue();
    });

    it('denies manager and user from deleting payments', function () {
        expect($this->manager->can('delete', $this->payment))->toBeFalse();
        expect($this->user->can('delete', $this->payment))->toBeFalse();
    });

    it('denies deleting payments from other organizations', function () {
        $otherPayment = Payment::factory()->for($this->otherOrganization)->create();

        expect($this->owner->can('delete', $otherPayment))->toBeFalse();
    });
});
