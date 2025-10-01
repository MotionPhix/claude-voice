<?php

use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->organization = Organization::factory()->create();

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
});

describe('OrganizationPolicy viewAny', function () {
    it('allows all authenticated users to view organizations', function () {
        expect($this->owner->can('viewAny', Organization::class))->toBeTrue();
        expect($this->user->can('viewAny', Organization::class))->toBeTrue();
    });
});

describe('OrganizationPolicy view', function () {
    it('allows members to view their organization', function () {
        expect($this->owner->can('view', $this->organization))->toBeTrue();
        expect($this->admin->can('view', $this->organization))->toBeTrue();
        expect($this->manager->can('view', $this->organization))->toBeTrue();
        expect($this->accountant->can('view', $this->organization))->toBeTrue();
        expect($this->user->can('view', $this->organization))->toBeTrue();
    });

    it('denies non-members from viewing an organization', function () {
        $nonMember = User::factory()->create();

        expect($nonMember->can('view', $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy create', function () {
    it('allows any authenticated user to create an organization', function () {
        $newUser = User::factory()->create();

        expect($newUser->can('create', Organization::class))->toBeTrue();
    });
});

describe('OrganizationPolicy update', function () {
    it('allows owner and admin to update organization', function () {
        expect($this->owner->can('update', $this->organization))->toBeTrue();
        expect($this->admin->can('update', $this->organization))->toBeTrue();
    });

    it('denies manager, accountant, and user from updating organization', function () {
        expect($this->manager->can('update', $this->organization))->toBeFalse();
        expect($this->accountant->can('update', $this->organization))->toBeFalse();
        expect($this->user->can('update', $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy delete', function () {
    it('allows only owner to delete organization', function () {
        expect($this->owner->can('delete', $this->organization))->toBeTrue();
    });

    it('denies non-owners from deleting organization', function () {
        expect($this->admin->can('delete', $this->organization))->toBeFalse();
        expect($this->manager->can('delete', $this->organization))->toBeFalse();
        expect($this->accountant->can('delete', $this->organization))->toBeFalse();
        expect($this->user->can('delete', $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy manageBilling', function () {
    it('allows only owner to manage billing', function () {
        expect($this->owner->can('manageBilling', $this->organization))->toBeTrue();
    });

    it('denies non-owners from managing billing', function () {
        expect($this->admin->can('manageBilling', $this->organization))->toBeFalse();
        expect($this->manager->can('manageBilling', $this->organization))->toBeFalse();
        expect($this->accountant->can('manageBilling', $this->organization))->toBeFalse();
        expect($this->user->can('manageBilling', $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy manageMembers', function () {
    it('allows owner and admin to manage members', function () {
        expect($this->owner->can('manageMembers', $this->organization))->toBeTrue();
        expect($this->admin->can('manageMembers', $this->organization))->toBeTrue();
    });

    it('denies other roles from managing members', function () {
        expect($this->manager->can('manageMembers', $this->organization))->toBeFalse();
        expect($this->accountant->can('manageMembers', $this->organization))->toBeFalse();
        expect($this->user->can('manageMembers', $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy inviteMembers', function () {
    it('allows owner and admin to invite members', function () {
        expect($this->owner->can('inviteMembers', $this->organization))->toBeTrue();
        expect($this->admin->can('inviteMembers', $this->organization))->toBeTrue();
    });

    it('denies other roles from inviting members', function () {
        expect($this->manager->can('inviteMembers', $this->organization))->toBeFalse();
        expect($this->accountant->can('inviteMembers', $this->organization))->toBeFalse();
        expect($this->user->can('inviteMembers', $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy removeMembers', function () {
    it('allows owner and admin to remove members', function () {
        expect($this->owner->can('removeMembers', $this->organization))->toBeTrue();
        expect($this->admin->can('removeMembers', $this->organization))->toBeTrue();
    });

    it('denies other roles from removing members', function () {
        expect($this->manager->can('removeMembers', $this->organization))->toBeFalse();
        expect($this->accountant->can('removeMembers', $this->organization))->toBeFalse();
        expect($this->user->can('removeMembers', $this->organization))->toBeFalse();
    });
});

describe('OrganizationPolicy updateSettings', function () {
    it('allows owner and admin to update settings', function () {
        expect($this->owner->can('updateSettings', $this->organization))->toBeTrue();
        expect($this->admin->can('updateSettings', $this->organization))->toBeTrue();
    });

    it('denies other roles from updating settings', function () {
        expect($this->manager->can('updateSettings', $this->organization))->toBeFalse();
        expect($this->accountant->can('updateSettings', $this->organization))->toBeFalse();
        expect($this->user->can('updateSettings', $this->organization))->toBeFalse();
    });
});
