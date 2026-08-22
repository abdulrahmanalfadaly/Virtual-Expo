<?php

namespace App\Notifications;

use App\Models\ActivityLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class AdminActivityNotification extends Notification implements ShouldBroadcastNow
{
    public function __construct(public ActivityLog $log)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'action' => $this->log->action,
            'description' => $this->log->description,
            'url' => $this->log->metadata['url'] ?? null,
            'school_id' => $this->log->school_id,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'action' => $this->log->action,
            'description' => $this->log->description,
            'url' => $this->log->metadata['url'] ?? null,
            'created_at' => $this->log->created_at->toIso8601String(),
        ]);
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin-notifications')];
    }

    public function broadcastAs(): string
    {
        return 'admin.notification';
    }
}
