<?php

namespace App\Listeners;

use App\Events\StudentAppliedToJob;
use App\Models\Postulacion;
use App\Services\NotificationDeliveryService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

class NotifyCompanyOfNewApplication implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(private readonly NotificationDeliveryService $deliveries) {}

    public function handle(StudentAppliedToJob $event): void
    {
        $postulacion = Postulacion::with('vacante.empresa.user', 'estudiante.user')->find($event->applicationId);
        if (! $postulacion) {
            return;
        }

        $vacante = $postulacion->vacante;
        $recipient = $vacante?->empresa?->user;

        if (! $vacante || ! $recipient) {
            return;
        }

        $this->deliveries->sendOnce(
            $recipient,
            "application.received:{$postulacion->id}:user:{$recipient->id}",
            [
                'event' => 'application.received',
                'title' => 'Nueva postulación recibida',
                'message' => $postulacion->estudiante->user->name.' se ha postulado a tu vacante de '.$vacante->titulo.'.',
                'vacancy_id' => $vacante->id,
                'application_id' => $postulacion->id,
            ],
        );
    }
}
