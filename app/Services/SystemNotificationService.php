<?php

namespace App\Services;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SystemNotificationService
{
    /**
     * Create a new system notification
     */
    public function create(
        string $type,
        string $title,
        string $message,
        User $user = null,
        Model $notifiable = null,
        string $level = 'info',
        array $data = [],
        ?\DateTimeInterface $expiresAt = null
    ): SystemNotification {
        return SystemNotification::create([
            'type' => $type,
            'level' => $level,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'user_id' => $user?->id,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id' => $notifiable?->id,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Create notification for invoice overdue
     */
    public function notifyInvoiceOverdue($invoice, User $user = null): SystemNotification
    {
        return $this->create(
            type: 'invoice_overdue',
            title: 'Invoice Overdue',
            message: "Invoice #{$invoice->number} is overdue. Due date was {$invoice->due_date->format('M d, Y')}.",
            user: $user,
            notifiable: $invoice,
            level: 'warning',
            data: [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->number,
                'due_date' => $invoice->due_date->toISOString(),
                'amount' => $invoice->total,
                'client_name' => $invoice->client->name,
            ]
        );
    }

    /**
     * Create notification for payment received
     */
    public function notifyPaymentReceived($payment, User $user = null): SystemNotification
    {
        return $this->create(
            type: 'payment_received',
            title: 'Payment Received',
            message: "Payment of {$payment->amount_formatted} received for Invoice #{$payment->invoice->number}.",
            user: $user,
            notifiable: $payment,
            level: 'success',
            data: [
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice->id,
                'invoice_number' => $payment->invoice->number,
                'amount' => $payment->amount,
                'client_name' => $payment->invoice->client->name,
            ]
        );
    }

    /**
     * Create notification for invoice sent
     */
    public function notifyInvoiceSent($invoice, User $user = null): SystemNotification
    {
        return $this->create(
            type: 'invoice_sent',
            title: 'Invoice Sent',
            message: "Invoice #{$invoice->number} has been sent to {$invoice->client->name}.",
            user: $user,
            notifiable: $invoice,
            level: 'success',
            data: [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->number,
                'client_name' => $invoice->client->name,
                'amount' => $invoice->total,
            ]
        );
    }

    /**
     * Create notification for new client
     */
    public function notifyClientCreated($client, User $user = null): SystemNotification
    {
        return $this->create(
            type: 'client_created',
            title: 'New Client Added',
            message: "New client '{$client->name}' has been added to your system.",
            user: $user,
            notifiable: $client,
            level: 'info',
            data: [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'client_email' => $client->email,
            ]
        );
    }

    /**
     * Create notification for recurring invoice generated
     */
    public function notifyRecurringInvoiceGenerated($invoice, $recurringInvoice, User $user = null): SystemNotification
    {
        return $this->create(
            type: 'recurring_invoice_generated',
            title: 'Recurring Invoice Generated',
            message: "Recurring invoice generated: #{$invoice->number} for {$invoice->client->name}.",
            user: $user,
            notifiable: $invoice,
            level: 'info',
            data: [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->number,
                'recurring_invoice_id' => $recurringInvoice->id,
                'client_name' => $invoice->client->name,
                'amount' => $invoice->total,
            ]
        );
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(User $user, int $limit = 50): Collection
    {
        return SystemNotification::where('user_id', $user->id)
            ->undismissed()
            ->active()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get system-wide notifications
     */
    public function getSystemNotifications(int $limit = 50): Collection
    {
        return SystemNotification::whereNull('user_id')
            ->undismissed()
            ->active()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(User $user): int
    {
        return SystemNotification::where('user_id', $user->id)
            ->unread()
            ->undismissed()
            ->active()
            ->count();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsReadForUser(User $user): int
    {
        return SystemNotification::where('user_id', $user->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Clean up expired notifications
     */
    public function cleanupExpiredNotifications(): int
    {
        return SystemNotification::where('expires_at', '<', now())
            ->delete();
    }

    /**
     * Clean up old dismissed notifications (older than 30 days)
     */
    public function cleanupOldDismissedNotifications(): int
    {
        return SystemNotification::where('is_dismissed', true)
            ->where('dismissed_at', '<', now()->subDays(30))
            ->delete();
    }
}
