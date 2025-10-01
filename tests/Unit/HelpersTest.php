<?php

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
    $this->membership = Membership::factory()->owner()->create([
        'user_id' => $this->user->id,
        'organization_id' => $this->organization->id,
    ]);
});

describe('current_organization_id helper', function () {
    it('returns null when no organization is set', function () {
        expect(current_organization_id())->toBeNull();
    });

    it('returns organization id when set in session', function () {
        set_current_organization($this->organization);

        expect(current_organization_id())->toBe($this->organization->id);
    });

    it('returns organization id from integer', function () {
        set_current_organization($this->organization->id);

        expect(current_organization_id())->toBe($this->organization->id);
    });
});

describe('current_organization helper', function () {
    it('returns null when no organization is set', function () {
        expect(current_organization())->toBeNull();
    });

    it('returns organization model when set', function () {
        set_current_organization($this->organization);

        $org = current_organization();

        expect($org)->toBeInstanceOf(Organization::class);
        expect($org->id)->toBe($this->organization->id);
    });

    it('caches organization model', function () {
        set_current_organization($this->organization);

        $org1 = current_organization();
        $org2 = current_organization();

        expect($org1)->toBe($org2); // Same instance
    });
});

describe('set_current_organization helper', function () {
    it('sets organization from model', function () {
        set_current_organization($this->organization);

        expect(session('current_organization_id'))->toBe($this->organization->id);
    });

    it('sets organization from id', function () {
        set_current_organization($this->organization->id);

        expect(session('current_organization_id'))->toBe($this->organization->id);
    });

    it('clears organization when null', function () {
        set_current_organization($this->organization);
        expect(session('current_organization_id'))->toBe($this->organization->id);

        set_current_organization(null);
        expect(session('current_organization_id'))->toBeNull();
    });
});

describe('user_organizations helper', function () {
    it('returns empty collection for guest', function () {
        expect(user_organizations())->toBeEmpty();
    });

    it('returns user organizations when authenticated', function () {
        $this->actingAs($this->user);

        $orgs = user_organizations();

        expect($orgs)->toHaveCount(1);
        expect($orgs->first()->id)->toBe($this->organization->id);
    });

    it('returns multiple organizations for multi-org user', function () {
        $org2 = Organization::factory()->create();
        Membership::factory()->manager()->create([
            'user_id' => $this->user->id,
            'organization_id' => $org2->id,
        ]);

        $this->actingAs($this->user);

        $orgs = user_organizations();

        expect($orgs)->toHaveCount(2);
    });
});

describe('current_membership helper', function () {
    it('returns null when no user authenticated', function () {
        set_current_organization($this->organization);

        expect(current_membership())->toBeNull();
    });

    it('returns null when no organization set', function () {
        $this->actingAs($this->user);

        expect(current_membership())->toBeNull();
    });

    it('returns membership when user and organization set', function () {
        $this->actingAs($this->user);
        set_current_organization($this->organization);

        $membership = current_membership();

        expect($membership)->toBeInstanceOf(Membership::class);
        expect($membership->user_id)->toBe($this->user->id);
        expect($membership->organization_id)->toBe($this->organization->id);
    });

    it('returns correct membership for specific organization', function () {
        $org2 = Organization::factory()->create();
        $membership2 = Membership::factory()->manager()->create([
            'user_id' => $this->user->id,
            'organization_id' => $org2->id,
        ]);

        $this->actingAs($this->user);
        set_current_organization($org2);

        $currentMembership = current_membership();

        expect($currentMembership->id)->toBe($membership2->id);
        expect($currentMembership->role)->toBe(MembershipRole::Manager);
    });
});

describe('user_can_in_organization helper', function () {
    it('returns false when no membership', function () {
        $this->actingAs($this->user);

        expect(user_can_in_organization('invoices.create'))->toBeFalse();
    });

    it('returns true for allowed permission', function () {
        $this->actingAs($this->user);
        set_current_organization($this->organization);

        // Owner can create invoices
        expect(user_can_in_organization('invoices.create'))->toBeTrue();
    });

    it('returns false for disallowed permission', function () {
        // Create user with 'User' role
        $userRole = User::factory()->create();
        Membership::factory()->user()->create([
            'user_id' => $userRole->id,
            'organization_id' => $this->organization->id,
        ]);

        $this->actingAs($userRole);
        set_current_organization($this->organization);

        // User role cannot create invoices
        expect(user_can_in_organization('invoices.create'))->toBeFalse();
    });
});

describe('current_user_role helper', function () {
    it('returns null when no membership', function () {
        $this->actingAs($this->user);

        expect(current_user_role())->toBeNull();
    });

    it('returns user role when membership exists', function () {
        $this->actingAs($this->user);
        set_current_organization($this->organization);

        $role = current_user_role();

        expect($role)->toBeInstanceOf(MembershipRole::class);
        expect($role)->toBe(MembershipRole::Owner);
    });
});

describe('user_has_role helper', function () {
    it('returns false when no membership', function () {
        $this->actingAs($this->user);

        expect(user_has_role('owner'))->toBeFalse();
    });

    it('returns true for matching role', function () {
        $this->actingAs($this->user);
        set_current_organization($this->organization);

        expect(user_has_role('owner'))->toBeTrue();
        expect(user_has_role(MembershipRole::Owner))->toBeTrue();
    });

    it('returns false for non-matching role', function () {
        $this->actingAs($this->user);
        set_current_organization($this->organization);

        expect(user_has_role('manager'))->toBeFalse();
        expect(user_has_role(MembershipRole::Manager))->toBeFalse();
    });
});

describe('ensure_organization helper', function () {
    it('sets first organization when none is set', function () {
        $this->actingAs($this->user);

        expect(current_organization_id())->toBeNull();

        ensure_organization();

        expect(current_organization_id())->toBe($this->organization->id);
    });

    it('keeps existing organization when already set', function () {
        $org2 = Organization::factory()->create();
        Membership::factory()->manager()->create([
            'user_id' => $this->user->id,
            'organization_id' => $org2->id,
        ]);

        $this->actingAs($this->user);
        set_current_organization($this->organization);

        ensure_organization();

        expect(current_organization_id())->toBe($this->organization->id);
    });

    it('does nothing when user has no organizations', function () {
        $newUser = User::factory()->create();
        $this->actingAs($newUser);

        ensure_organization();

        expect(current_organization_id())->toBeNull();
    });
});

describe('organization helper edge cases', function () {
    it('handles rapid organization switching', function () {
        $org2 = Organization::factory()->create();
        $org3 = Organization::factory()->create();

        set_current_organization($this->organization);
        expect(current_organization_id())->toBe($this->organization->id);

        set_current_organization($org2);
        expect(current_organization_id())->toBe($org2->id);

        set_current_organization($org3);
        expect(current_organization_id())->toBe($org3->id);
    });

    it('handles deleted organization gracefully', function () {
        set_current_organization($this->organization);

        $orgId = $this->organization->id;
        $this->organization->delete();

        // Should return null for deleted organization
        expect(current_organization())->toBeNull();
    });
});
