<?php

namespace App\Traits;

use App\Models\Organization;
use App\Models\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    /**
     * Boot the trait and apply the global scope.
     */
    protected static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope);

        // Automatically set organization_id when creating
        static::creating(function ($model) {
            if (! $model->organization_id && $organizationId = current_organization_id()) {
                $model->organization_id = $organizationId;
            }
        });
    }

    /**
     * Get the organization that owns the model.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope a query to a specific organization.
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organizationId);
    }

    /**
     * Scope a query to all organizations (remove the global scope).
     */
    public function scopeAllOrganizations($query)
    {
        return $query->withoutGlobalScope(OrganizationScope::class);
    }
}
