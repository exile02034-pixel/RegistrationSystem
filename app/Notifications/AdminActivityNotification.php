<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminActivityNotification extends Notification
{
    use Queueable;

    /**
     * @param array{
     *   title: string,
     *   message: string,
     *   url?: string,
     *   type?: string
     * } $payload
     */
    public function __construct(private readonly array $payload)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->payload['title'],
            'message' => $this->payload['message'],
            'url' => $this->payload['url'] ?? null,
            'type' => $this->payload['type'] ?? 'info',
        ];
    }
}
