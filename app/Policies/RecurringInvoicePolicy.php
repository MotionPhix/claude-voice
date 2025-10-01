<?php

namespace App\Policies;

use App\Enums\MembershipRole;
use App\Models\RecurringInvoice;
use App\Models\User;

class RecurringInvoicePolicy
{
    /**
     * Determine if the user can view any recurring invoices.
     */
    public function viewAny(User $user): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // All roles can view recurring invoices
        return true;
    }

    /**
     * Determine if the user can view the recurring invoice.
     */
    public function view(User $user, RecurringInvoice $recurringInvoice): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Ensure recurring invoice belongs to current organization
        if ($recurringInvoice->organization_id !== current_organization_id()) {
            return false;
        }

        // All roles can view recurring invoices in their organization
        return true;
    }

    /**
     * Determine if the user can create recurring invoices.
     */
    public function create(User $user): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Owners, Admins, Managers can create recurring invoices
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Manager,
        ]);
    }

    /**
     * Determine if the user can update the recurring invoice.
     */
    public function update(User $user, RecurringInvoice $recurringInvoice): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Ensure recurring invoice belongs to current organization
        if ($recurringInvoice->organization_id !== current_organization_id()) {
            return false;
        }

        // Owners, Admins, Managers can update recurring invoices
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Manager,
        ]);
    }

    /**
     * Determine if the user can delete the recurring invoice.
     */
    public function delete(User $user, RecurringInvoice $recurringInvoice): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Ensure recurring invoice belongs to current organization
        if ($recurringInvoice->organization_id !== current_organization_id()) {
            return false;
        }

        // Owners, Admins can delete recurring invoices
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
        ]);
    }

    /**
     * Determine if the user can activate/deactivate the recurring invoice.
     */
    public function toggleStatus(User $user, RecurringInvoice $recurringInvoice): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Ensure recurring invoice belongs to current organization
        if ($recurringInvoice->organization_id !== current_organization_id()) {
            return false;
        }

        // Owners, Admins, Managers can toggle status
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Manager,
        ]);
    }

    /**
     * Determine if the user can manually generate an invoice from this recurring invoice.
     */
    public function generateInvoice(User $user, RecurringInvoice $recurringInvoice): bool
    {
        $membership = current_membership();

        if (! $membership) {
            return false;
        }

        // Ensure recurring invoice belongs to current organization
        if ($recurringInvoice->organization_id !== current_organization_id()) {
            return false;
        }

        // Owners, Admins, Managers can generate invoices
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Manager,
        ]);
    }
}
