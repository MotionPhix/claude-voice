<?php

namespace App\Policies;

use App\Enums\MembershipRole;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class InvoicePolicy
{
    /**
     * Resolve the membership for a user in a given organization.
     */
    protected function membershipFor(User $user, ?int $organizationId = null): ?Membership
    {
        $orgId = $organizationId ?? null;

        if (! $orgId) {
            // Try current organization helper as fallback
            if (function_exists('current_organization_id')) {
                $orgId = current_organization_id();
            }
        }

        if (! $orgId) {
            return null;
        }

        return Membership::where('user_id', $user->id)
            ->where('organization_id', $orgId)
            ->first();
    }

    /**
     * Determine if the user can view any invoices.
     */
    public function viewAny(User $user): bool
    {
        $membership = $this->membershipFor($user);

        if (! $membership) {
            return false;
        }

        // All roles can view invoices
        return true;
    }

    /**
     * Determine if the user can view the invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        $membership = $this->membershipFor($user, $invoice->organization_id);

        if (! $membership) {
            return false;
        }

        // Ensure invoice belongs to current organization context
        if ($invoice->organization_id !== $membership->organization_id) {
            return false;
        }

        // All roles can view invoices in their organization
        return true;
    }

    /**
     * Determine if the user can create invoices.
     */
    public function create(User $user): bool
    {
        $membership = $this->membershipFor($user);

        if (! $membership) {
            return false;
        }

        // Owners, Admins, Managers can create invoices
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Manager,
        ]);
    }

    /**
     * Determine if the user can update the invoice.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        $membership = $this->membershipFor($user, $invoice->organization_id);

        if (! $membership) {
            return false;
        }

        // Ensure invoice belongs to same organization
        if ($invoice->organization_id !== $membership->organization_id) {
            return false;
        }

        // Owners, Admins, Managers, Accountants can update invoices
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Manager,
            MembershipRole::Accountant,
        ]);
    }

    /**
     * Determine if the user can delete the invoice.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        $membership = $this->membershipFor($user, $invoice->organization_id);

        if (! $membership) {
            return false;
        }

        if ($invoice->organization_id !== $membership->organization_id) {
            return false;
        }

        // Only draft invoices can be deleted
        if ($invoice->status !== 'draft') {
            return true; // allow controller to return friendly message; authorization passes so controller can handle business rule
        }

        // Owners, Admins, Managers can delete invoices
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Manager,
        ]);
    }

    /**
     * Determine if the user can send the invoice.
     */
    public function send(User $user, Invoice $invoice): bool
    {
        $membership = $this->membershipFor($user, $invoice->organization_id);

        if (! $membership) {
            return false;
        }

        if ($invoice->organization_id !== $membership->organization_id) {
            return false;
        }

        // Owners, Admins, Managers can send invoices
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Manager,
        ]);
    }

    /**
     * Determine if the user can duplicate the invoice.
     */
    public function duplicate(User $user, Invoice $invoice): bool
    {
        $membership = $this->membershipFor($user, $invoice->organization_id);

        if (! $membership) {
            return false;
        }

        if ($invoice->organization_id !== $membership->organization_id) {
            return false;
        }

        // Owners, Admins, Managers can duplicate invoices
        return $membership->hasAnyRole([
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Manager,
        ]);
    }

    /**
     * Determine if the user can download the invoice PDF.
     */
    public function downloadPdf(User $user, Invoice $invoice): bool
    {
        $membership = $this->membershipFor($user, $invoice->organization_id);

        if (! $membership) {
            return false;
        }

        if ($invoice->organization_id !== $membership->organization_id) {
            return false;
        }

        // All roles can download PDFs
        return true;
    }
}
