<?php

namespace App\Events;

use App\Models\SystemNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class SystemNotificationCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public SystemNotification $notification
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [];
        
        if ($this->notification->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->notification->user_id);
        } else {
            // System-wide notifications (broadcast to all users)
            $channels[] = new PrivateChannel('system');
        }
        
        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'SystemNotificationCreated';
    }

    /**
     * The event's broadcast data.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'level' => $this->notification->level,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'data' => $this->notification->data,
            'icon' => $this->notification->icon,
            'level_color' => $this->notification->level_color,
            'notifiable_type' => $this->notification->notifiable_type,
            'notifiable_id' => $this->notification->notifiable_id,
            'is_read' => $this->notification->is_read,
            'is_dismissed' => $this->notification->is_dismissed,
            'created_at' => $this->notification->created_at?->toISOString(),
        ];
    }
}
