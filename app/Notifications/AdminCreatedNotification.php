<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu cuenta de administrador está lista')
            ->view([
                'html' => 'emails.admin-created',
                'text' => 'emails.admin-created-text',
            ], [
                'name' => $notifiable->name,
                'email' => $notifiable->email,
                'passwordUrl' => route('password.reset', [
                    'token' => $this->token,
                    'email' => $notifiable->email,
                ]),
                'loginUrl' => route('login'),
            ]);
    }
}
