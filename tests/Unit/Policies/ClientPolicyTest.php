<?php

use App\Models\Client;
use App\Models\Membership;
use App\Models\Organization;
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

    $this->client = Client::factory()->for($this->organization)->create();
});

describe('ClientPolicy viewAny', function () {
    it('allows all roles to view clients', function () {
        $this->actingAs($this->owner);
        expect($this->owner->can('viewAny', Client::class))->toBeTrue();

        $this->actingAs($this->admin);
        expect($this->admin->can('viewAny', Client::class))->toBeTrue();

        $this->actingAs($this->manager);
        expect($this->manager->can('viewAny', Client::class))->toBeTrue();

        $this->actingAs($this->accountant);
        expect($this->accountant->can('viewAny', Client::class))->toBeTrue();

        $this->actingAs($this->user);
        expect($this->user->can('viewAny', Client::class))->toBeTrue();
    });
});

describe('ClientPolicy view', function () {
    it('allows all roles to view clients in their organization', function () {
        $this->actingAs($this->owner);
        expect($this->owner->can('view', $this->client))->toBeTrue();

        $this->actingAs($this->admin);
        expect($this->admin->can('view', $this->client))->toBeTrue();

        $this->actingAs($this->manager);
        expect($this->manager->can('view', $this->client))->toBeTrue();

        $this->actingAs($this->accountant);
        expect($this->accountant->can('view', $this->client))->toBeTrue();

        $this->actingAs($this->user);
        expect($this->user->can('view', $this->client))->toBeTrue();
    });

    it('denies viewing clients from other organizations', function () {
        $otherClient = Client::factory()->for($this->otherOrganization)->create();

        $this->actingAs($this->owner);
        expect($this->owner->can('view', $otherClient))->toBeFalse();
    });
});

describe('ClientPolicy create', function () {
    it('allows owner, admin, and manager to create clients', function () {
        $this->actingAs($this->owner);
        expect($this->owner->can('create', Client::class))->toBeTrue();

        $this->actingAs($this->admin);
        expect($this->admin->can('create', Client::class))->toBeTrue();

        $this->actingAs($this->manager);
        expect($this->manager->can('create', Client::class))->toBeTrue();
    });

    it('denies accountant and user from creating clients', function () {
        $this->actingAs($this->accountant);
        expect($this->accountant->can('create', Client::class))->toBeFalse();

        $this->actingAs($this->user);
        expect($this->user->can('create', Client::class))->toBeFalse();
    });
});

describe('ClientPolicy update', function () {
    it('allows owner, admin, and manager to update clients', function () {
        $this->actingAs($this->owner);
        expect($this->owner->can('update', $this->client))->toBeTrue();

        $this->actingAs($this->admin);
        expect($this->admin->can('update', $this->client))->toBeTrue();

        $this->actingAs($this->manager);
        expect($this->manager->can('update', $this->client))->toBeTrue();
    });

    it('denies accountant and user from updating clients', function () {
        $this->actingAs($this->accountant);
        expect($this->accountant->can('update', $this->client))->toBeFalse();

        $this->actingAs($this->user);
        expect($this->user->can('update', $this->client))->toBeFalse();
    });

    it('denies updating clients from other organizations', function () {
        $otherClient = Client::factory()->for($this->otherOrganization)->create();

        $this->actingAs($this->owner);
        expect($this->owner->can('update', $otherClient))->toBeFalse();
    });
});

describe('ClientPolicy delete', function () {
    it('allows owner and admin to delete clients', function () {
        $this->actingAs($this->owner);
        expect($this->owner->can('delete', $this->client))->toBeTrue();

        $this->actingAs($this->admin);
        expect($this->admin->can('delete', $this->client))->toBeTrue();
    });

    it('denies manager, accountant, and user from deleting clients', function () {
        $this->actingAs($this->manager);
        expect($this->manager->can('delete', $this->client))->toBeFalse();

        $this->actingAs($this->accountant);
        expect($this->accountant->can('delete', $this->client))->toBeFalse();

        $this->actingAs($this->user);
        expect($this->user->can('delete', $this->client))->toBeFalse();
    });

    it('denies deleting clients from other organizations', function () {
        $otherClient = Client::factory()->for($this->otherOrganization)->create();

        $this->actingAs($this->owner);
        expect($this->owner->can('delete', $otherClient))->toBeFalse();
    });
});
