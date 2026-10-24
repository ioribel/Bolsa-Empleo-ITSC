<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PlatformDatabaseNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly array $notificationData) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->notificationData;
    }
}
