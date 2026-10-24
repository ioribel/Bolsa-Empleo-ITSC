<?php

namespace App\Listeners;

use App\Events\ApplicationStatusChanged;
use App\Models\Postulacion;
use App\Services\NotificationDeliveryService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

class NotifyStudentOfApplicationStatus implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(private readonly NotificationDeliveryService $deliveries) {}

    public function handle(ApplicationStatusChanged $event): void
    {
        if (! in_array($event->newStatus, ['aceptada', 'rechazada'], true)
            || $event->previousStatus === $event->newStatus) {
            return;
        }

        $postulacion = Postulacion::with('estudiante.user', 'vacante.empresa')->find($event->applicationId);
        if (! $postulacion) {
            return;
        }

        $recipient = $postulacion->estudiante?->user;
        $vacante = $postulacion->vacante;

        if (! $recipient || ! $vacante) {
            return;
        }

        $accepted = $event->newStatus === 'aceptada';
        $this->deliveries->sendOnce(
            $recipient,
            "application.status:{$postulacion->id}:{$event->newStatus}:user:{$recipient->id}",
            [
                'event' => 'application.'.$event->newStatus,
                'title' => $accepted ? '¡Tu postulación fue aprobada!' : 'Actualización de tu postulación',
                'message' => $accepted
                    ? 'Tu postulación para la vacante de '.$vacante->titulo.' ha sido aprobada por '.$vacante->empresa->nombre_empresa.'.'
                    : 'Tu postulación para la vacante de '.$vacante->titulo.' ha sido marcada como no seleccionada.',
                'vacancy_id' => $vacante->id,
                'application_id' => $postulacion->id,
                'status' => $event->newStatus,
            ],
        );
    }
}
