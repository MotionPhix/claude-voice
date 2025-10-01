<?php

namespace App\Policies;

use App\Enums\MembershipRole;
use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Determine if the user can view any organizations.
     */
    public function viewAny(User $user): bool
    {
        // Users can always view organizations they belong to
        return true;
    }

    /**
     * Determine if the user can view the organization.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $user->belongsToOrganization($organization);
    }

    /**
     * Determine if the user can create organizations.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create an organization
        return true;
    }

    /**
     * Determine if the user can update the organization.
     */
    public function update(User $user, Organization $organization): bool
    {
        $membership = $user->membershipInOrganization($organization);

        if (! $membership) {
            return false;
        }

        // Only owners and admins can update organization
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
        ]);
    }

    /**
     * Determine if the user can delete the organization.
     */
    public function delete(User $user, Organization $organization): bool
    {
        $membership = $user->membershipInOrganization($organization);

        if (! $membership) {
            return false;
        }

        // Only owners can delete organization
        return $membership->hasRole(MembershipRole::Owner);
    }

    /**
     * Determine if the user can manage billing for the organization.
     */
    public function manageBilling(User $user, Organization $organization): bool
    {
        $membership = $user->membershipInOrganization($organization);

        if (! $membership) {
            return false;
        }

        // Only owners can manage billing
        return $membership->hasRole(MembershipRole::Owner);
    }

    /**
     * Determine if the user can manage members of the organization.
     */
    public function manageMembers(User $user, Organization $organization): bool
    {
        $membership = $user->membershipInOrganization($organization);

        if (! $membership) {
            return false;
        }

        // Owners, admins can manage members
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
        ]);
    }

    /**
     * Determine if the user can invite members to the organization.
     */
    public function inviteMembers(User $user, Organization $organization): bool
    {
        $membership = $user->membershipInOrganization($organization);

        if (! $membership) {
            return false;
        }

        // Owners and admins can invite members
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
        ]);
    }

    /**
     * Determine if the user can remove members from the organization.
     */
    public function removeMembers(User $user, Organization $organization): bool
    {
        $membership = $user->membershipInOrganization($organization);

        if (! $membership) {
            return false;
        }

        // Owners and admins can remove members
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
        ]);
    }

    /**
     * Determine if the user can view settings for the organization.
     */
    public function viewSettings(User $user, Organization $organization): bool
    {
        return $user->belongsToOrganization($organization);
    }

    /**
     * Determine if the user can update settings for the organization.
     */
    public function updateSettings(User $user, Organization $organization): bool
    {
        $membership = $user->membershipInOrganization($organization);

        if (! $membership) {
            return false;
        }

        // Owners and admins can update settings
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
        ]);
    }
}
