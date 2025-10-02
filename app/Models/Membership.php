<?php

namespace App\Models;

use App\Enums\MembershipRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'role',
        'is_active',
        'invited_email',
        'invitation_token',
        'invitation_sent_at',
        'invitation_accepted_at',
        'invitation_expires_at',
        'invited_by_id',
    ];

    // Use proper $casts property so Laravel applies enum and datetime casting correctly
    protected $casts = [
        'role' => MembershipRole::class,
        'is_active' => 'boolean',
        'invitation_sent_at' => 'datetime',
        'invitation_accepted_at' => 'datetime',
        'invitation_expires_at' => 'datetime',
    ];

    /**
     * Get the user that belongs to this membership.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization that this membership belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user who invited this member.
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_id');
    }

    /**
     * Check if the membership is pending (invitation not accepted).
     */
    public function isPending(): bool
    {
        return $this->user_id === null && $this->invitation_token !== null;
    }

    /**
     * Check if the invitation has expired.
     */
    public function isInvitationExpired(): bool
    {
        return $this->invitation_expires_at && $this->invitation_expires_at->isPast();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(MembershipRole|string $role): bool
    {
        // Normalize stored role
        $stored = $this->role;

        if ($stored instanceof MembershipRole) {
            if ($role instanceof MembershipRole) {
                return $stored === $role;
            }

            return $stored->value === (string) $role;
        }

        // stored as scalar (string)
        $storedValue = (string) $stored;

        if ($role instanceof MembershipRole) {
            return $storedValue === $role->value;
        }

        return $storedValue === (string) $role;
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can perform a specific permission.
     */
    public function can(string $permission): bool
    {
        // If role is enum instance, delegate; if string, try mapping via enum
        $role = $this->role;

        if ($role instanceof MembershipRole) {
            return $role->can($permission);
        }

        // Attempt to convert stored string to enum
        try {
            $roleEnum = MembershipRole::from($role);
            return $roleEnum->can($permission);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Scope to only active memberships.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to only pending invitations.
     */
    public function scopePending($query)
    {
        return $query->whereNull('user_id')
            ->whereNotNull('invitation_token');
    }

    /**
     * Scope to accepted memberships.
     */
    public function scopeAccepted($query)
    {
        return $query->whereNotNull('user_id')
            ->whereNotNull('invitation_accepted_at');
    }
}
