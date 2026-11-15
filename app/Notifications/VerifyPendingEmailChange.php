<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyPendingEmailChange extends Notification
{
    use Queueable;

    public function __construct(public string $verificationUrl) {}

    /**
     * Get the delivery channels for the notification.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirma tu nuevo correo electrónico')
            ->greeting('Hola,')
            ->line('Recibimos una solicitud para cambiar el correo electrónico de tu cuenta.')
            ->action('Confirmar nuevo correo', $this->verificationUrl)
            ->line('El enlace vence en 60 minutos. Tu correo actual seguirá activo hasta que confirmes el cambio.')
            ->line('Si no solicitaste este cambio, puedes ignorar este mensaje.');
    }
}
