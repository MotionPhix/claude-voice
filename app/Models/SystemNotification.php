<?php

namespace App\Models;

use App\Events\SystemNotificationCreated;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SystemNotification extends Model
{
    use BelongsToOrganization, HasUuid;

    protected $fillable = [
        'organization_id',
        'type',
        'level',
        'title',
        'message',
        'data',
        'user_id',
        'notifiable_type',
        'notifiable_id',
        'is_read',
        'is_dismissed',
        'read_at',
        'dismissed_at',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_dismissed' => 'boolean',
        'read_at' => 'datetime',
        'dismissed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($notification) {
            broadcast(new SystemNotificationCreated($notification));
        });
    }

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notifiable model (Invoice, Client, Payment, etc.)
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark the notification as read
     */
    public function markAsRead(): self
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark the notification as dismissed
     */
    public function dismiss(): self
    {
        $this->update([
            'is_dismissed' => true,
            'dismissed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for undismissed notifications
     */
    public function scopeUndismissed($query)
    {
        return $query->where('is_dismissed', false);
    }

    /**
     * Scope for active notifications (not expired)
     */
    public function scopeActive($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for specific notification types
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the CSS class for the notification level
     */
    public function getLevelColorAttribute(): string
    {
        return match ($this->level) {
            'success' => 'text-green-600 bg-green-50 border-green-200',
            'warning' => 'text-yellow-600 bg-yellow-50 border-yellow-200',
            'error' => 'text-red-600 bg-red-50 border-red-200',
            default => 'text-blue-600 bg-blue-50 border-blue-200',
        };
    }

    /**
     * Get the icon for the notification type
     */
    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'invoice_overdue' => 'alert-triangle',
            'payment_received' => 'check-circle',
            'invoice_sent' => 'send',
            'client_created' => 'user-plus',
            'recurring_invoice_generated' => 'refresh-cw',
            default => 'bell',
        };
    }
}
