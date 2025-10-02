<?php

use App\Models\Membership;
use App\Models\Organization;
use App\Enums\MembershipRole;
use Illuminate\Support\Facades\Auth;

if (! function_exists('current_organization_id')) {
    /**
     * Get the current organization ID from the session.
     */
    function current_organization_id(): ?int
    {
        $id = session('current_organization_id');

        if ($id === null) {
            return null;
        }

        return (int) $id;
    }
}

if (! function_exists('current_organization')) {
    /**
     * Get the current organization model. Cached per-request to avoid multiple DB hits.
     */
    function current_organization(): ?Organization
    {
        static $cached = null;

        $id = current_organization_id();

        if (! $id) {
            return null;
        }

        if ($cached instanceof Organization && $cached->getKey() === $id) {
            // Ensure the organization still exists in the database without reloading the model
            if (! Organization::whereKey($id)->exists()) {
                session()->forget('current_organization_id');
                $cached = null;
                return null;
            }

            return $cached;
        }

        $cached = Organization::find($id);

        // If the organization was deleted, clear the session and cache.
        if (! $cached) {
            session()->forget('current_organization_id');
            return null;
        }

        return $cached;
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
            session(['current_organization_id' => (int) $organizationId]);
        } else {
            session()->forget('current_organization_id');
        }

        // Clear cached organization when changing it.
        // Using the same static cache as current_organization() isn't directly accessible here,
        // but clearing the request-level cache can be achieved by unsetting a known key if needed.
        // We'll rely on the next call to current_organization() to refresh its static cache.
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

        // Ensure we return a Collection (not a Relation instance)
        return Auth::user()->activeOrganizations()->get();
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
     * Returns the MembershipRole enum instance or null.
     */
    function current_user_role(): ?MembershipRole
    {
        $membership = current_membership();

        return $membership?->role;
    }
}

if (! function_exists('user_has_role')) {
    /**
     * Check if the current user has a specific role in the current organization.
     * Accepts either a MembershipRole enum or a string role value.
     */
    function user_has_role(MembershipRole|string $role): bool
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
