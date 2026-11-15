<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\NotificationDestinationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlatformDatabaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly array $notificationData)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->notificationData;
    }

    public function toMail(User $notifiable): MailMessage
    {
        $copy = $this->mailCopy();
        $destination = app(NotificationDestinationService::class)->for($notifiable, $this->notificationData);

        return (new MailMessage)
            ->subject($copy['subject'])
            ->greeting('Hola '.$notifiable->name)
            ->line($copy['intro'])
            ->line($this->notificationData['message'] ?? 'Tienes una nueva notificación en Bolsa de Empleo ITSC.')
            ->action($copy['action'], $destination)
            ->salutation('Equipo Bolsa de Empleo ITSC');
    }

    /**
     * @return array{subject: string, intro: string, action: string}
     */
    private function mailCopy(): array
    {
        $vacancyTitle = $this->notificationData['vacancy_title'] ?? 'la vacante';
        $companyName = $this->notificationData['company_name'] ?? 'la empresa';

        return match ($this->notificationData['event'] ?? null) {
            'vacancy.published' => [
                'subject' => "Nueva vacante para tu carrera: {$vacancyTitle}",
                'intro' => 'Se publicó una oportunidad laboral relacionada con tu carrera.',
                'action' => 'Ver vacante',
            ],
            'application.received' => [
                'subject' => "Nueva postulación: {$vacancyTitle}",
                'intro' => 'Has recibido una nueva postulación para una de tus vacantes.',
                'action' => 'Revisar postulación',
            ],
            'application.aceptada' => [
                'subject' => "Postulación aprobada: {$vacancyTitle}",
                'intro' => '¡Buenas noticias! La empresa quiere continuar contigo.',
                'action' => 'Ver mi postulación',
            ],
            'application.rechazada' => [
                'subject' => "Actualización de postulación: {$vacancyTitle}",
                'intro' => 'La empresa actualizó el estado de tu postulación.',
                'action' => 'Ver mis postulaciones',
            ],
            'admin.vacancy.published' => [
                'subject' => "Vacante publicada: {$vacancyTitle}",
                'intro' => 'Se publicó una nueva vacante en la plataforma.',
                'action' => 'Revisar vacante',
            ],
            'admin.company.pending' => [
                'subject' => "Empresa pendiente de aprobación: {$companyName}",
                'intro' => 'Una empresa solicitó registrarse y necesita revisión.',
                'action' => 'Revisar solicitud',
            ],
            default => [
                'subject' => $this->notificationData['title'] ?? 'Nueva notificación',
                'intro' => 'Tienes una nueva notificación en Bolsa de Empleo ITSC.',
                'action' => 'Ver mis notificaciones',
            ],
        };
    }
}
