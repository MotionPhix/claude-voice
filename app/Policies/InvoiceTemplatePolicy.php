<?php

namespace App\Policies;

use App\Models\InvoiceTemplate;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvoiceTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->activeOrganizations->count() > 0;
    }

    public function view(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $user->isMemberOf($invoiceTemplate->organization_id) && $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $user->isMemberOf($invoiceTemplate->organization_id) && $user->isAdmin();
    }

    public function delete(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $user->isMemberOf($invoiceTemplate->organization_id) && $user->isAdmin();
    }

    public function restore(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $user->isMemberOf($invoiceTemplate->organization_id) && $user->isAdmin();
    }

    public function forceDelete(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $user->isMemberOf($invoiceTemplate->organization_id) && $user->isAdmin();
    }
}
