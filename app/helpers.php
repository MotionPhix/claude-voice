<?php

use App\Models\Membership;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

if (! function_exists('current_organization_id')) {
    /**
     * Get the current organization ID from the session.
     */
    function current_organization_id(): ?int
    {
        return session('current_organization_id');
    }
}

if (! function_exists('current_organization')) {
    /**
     * Get the current organization model.
     */
    function current_organization(): ?Organization
    {
        $id = current_organization_id();

        if (! $id) {
            return null;
        }

        return Organization::find($id);
    }
}

if (! function_exists('set_current_organization')) {
    /**
     * Set the current organization in the session.
     */
    function set_current_organization(Organization|int|null $organization): void
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        if ($organizationId) {
            session(['current_organization_id' => $organizationId]);
        } else {
            session()->forget('current_organization_id');
        }
    }
}

if (! function_exists('user_organizations')) {
    /**
     * Get all organizations the current user belongs to.
     */
    function user_organizations(): \Illuminate\Support\Collection
    {
        if (! Auth::check()) {
            return collect();
        }

        return Auth::user()->activeOrganizations;
    }
}

if (! function_exists('current_membership')) {
    /**
     * Get the current user's membership in the current organization.
     */
    function current_membership(): ?Membership
    {
        if (! Auth::check() || ! current_organization_id()) {
            return null;
        }

        return Auth::user()->membershipInOrganization(current_organization_id());
    }
}

if (! function_exists('user_can_in_organization')) {
    /**
     * Check if the current user can perform a permission in the current organization.
     */
    function user_can_in_organization(string $permission): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        return $membership->can($permission);
    }
}

if (! function_exists('current_user_role')) {
    /**
     * Get the current user's role in the current organization.
     */
    function current_user_role(): ?string
    {
        $membership = current_membership();

        return $membership?->role->value;
    }
}

if (! function_exists('user_has_role')) {
    /**
     * Check if the current user has a specific role in the current organization.
     */
    function user_has_role(string $role): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        return $membership->hasRole($role);
    }
}

if (! function_exists('ensure_organization')) {
    /**
     * Ensure the current user has an active organization set.
     * If not, set the first available organization.
     */
    function ensure_organization(): void
    {
        if (! Auth::check()) {
            return;
        }

        if (! current_organization_id()) {
            $firstOrg = Auth::user()->activeOrganizations->first();

            if ($firstOrg) {
                set_current_organization($firstOrg);
            }
        }
    }
}
