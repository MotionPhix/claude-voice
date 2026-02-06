<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUuid, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all memberships for this user.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Get all active memberships for this user.
     */
    public function activeMemberships(): HasMany
    {
        return $this->hasMany(Membership::class)->where('is_active', true);
    }

    /**
     * Get all organizations this user belongs to through memberships.
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'memberships')
            ->withPivot(['role', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get all active organizations this user belongs to.
     */
    public function activeOrganizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'memberships')
            ->wherePivot('is_active', true)
            ->withPivot(['role', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get all memberships invited by this user.
     */
    public function invitedMemberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'invited_by_id');
    }

    /**
     * Check if user belongs to a specific organization.
     */
    public function belongsToOrganization(Organization|int $organization): bool
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        return $this->memberships()
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get the user's role in a specific organization.
     */
    public function roleInOrganization(Organization|int $organization): ?string
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        $membership = $this->memberships()
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->first();

        return $membership?->role;
    }

    /**
     * Get the user's membership in a specific organization.
     */
    public function membershipInOrganization(Organization|int $organization): ?Membership
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        return $this->memberships()
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->first();
    }
}
