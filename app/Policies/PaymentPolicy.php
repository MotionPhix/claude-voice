<?php

namespace App\Policies;

use App\Enums\MembershipRole;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine if the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // All roles can view payments
        return true;
    }

    /**
     * Determine if the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Ensure payment belongs to current organization
        if ($payment->organization_id !== current_organization_id()) {
            return false;
        }

        // All roles can view payments in their organization
        return true;
    }

    /**
     * Determine if the user can create payments.
     */
    public function create(User $user): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Owners, Admins, Managers, Accountants can create payments
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Manager,
            MembershipRole::Accountant,
        ]);
    }

    /**
     * Determine if the user can update the payment.
     */
    public function update(User $user, Payment $payment): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Ensure payment belongs to current organization
        if ($payment->organization_id !== current_organization_id()) {
            return false;
        }

        // Owners, Admins, Accountants can update payments
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Accountant,
        ]);
    }

    /**
     * Determine if the user can delete the payment.
     */
    public function delete(User $user, Payment $payment): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Ensure payment belongs to current organization
        if ($payment->organization_id !== current_organization_id()) {
            return false;
        }

        // Owners, Admins, Accountants can delete payments
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Accountant,
        ]);
    }
}
