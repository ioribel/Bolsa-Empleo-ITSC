<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmpresaAprobadaNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu empresa fue aprobada')
            ->greeting('¡Buenas noticias!')
            ->line('El perfil de tu empresa fue aprobado.')
            ->action('Iniciar sesión', route('login'))
            ->line('Ya puedes iniciar sesión y comenzar a publicar vacantes.');
    }
}