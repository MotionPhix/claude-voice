<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrganizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply if we have a current organization in the session
        if ($organizationId = $this->getCurrentOrganizationId()) {
            $builder->where("{$model->getTable()}.organization_id", $organizationId);
        }
    }

    /**
     * Get the current organization ID from the session or helper.
     */
    protected function getCurrentOrganizationId(): ?int
    {
        // Try to get from helper function first
        if (function_exists('current_organization_id')) {
            return current_organization_id();
        }

        // Fall back to session
        if (session()->has('current_organization_id')) {
            return session('current_organization_id');
        }

        return null;
    }
}
