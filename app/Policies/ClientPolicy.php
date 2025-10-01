<?php

namespace App\Policies;

use App\Enums\MembershipRole;
use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Determine if the user can view any clients.
     */
    public function viewAny(User $user): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // All roles can view clients
        return true;
    }

    /**
     * Determine if the user can view the client.
     */
    public function view(User $user, Client $client): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Ensure client belongs to current organization
        if ($client->organization_id !== current_organization_id()) {
            return false;
        }

        // All roles can view clients in their organization
        return true;
    }

    /**
     * Determine if the user can create clients.
     */
    public function create(User $user): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Owners, Admins, Managers can create clients
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Manager,
        ]);
    }

    /**
     * Determine if the user can update the client.
     */
    public function update(User $user, Client $client): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Ensure client belongs to current organization
        if ($client->organization_id !== current_organization_id()) {
            return false;
        }

        // Owners, Admins, Managers can update clients
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Manager,
        ]);
    }

    /**
     * Determine if the user can delete the client.
     */
    public function delete(User $user, Client $client): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Ensure client belongs to current organization
        if ($client->organization_id !== current_organization_id()) {
            return false;
        }

        // Owners, Admins can delete clients
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
        ]);
    }
}
