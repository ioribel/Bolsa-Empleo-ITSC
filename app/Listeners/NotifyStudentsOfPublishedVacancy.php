<?php

namespace App\Listeners;

use App\Events\JobVacancyPublished;
use App\Models\Estudiante;
use App\Models\Vacante;
use App\Services\NotificationDeliveryService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

class NotifyStudentsOfPublishedVacancy implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(private readonly NotificationDeliveryService $deliveries) {}

    public function handle(JobVacancyPublished $event): void
    {
        $vacante = Vacante::with('empresa', 'carreras')->find($event->vacancyId);
        if (! $vacante) {
            return;
        }

        if ($vacante->estado !== 'publicada' || $vacante->carreras->isEmpty()) {
            return;
        }

        $careerIds = $vacante->carreras->modelKeys();
        Estudiante::query()
            ->whereIn('carrera_id', $careerIds)
            ->with('user')
            ->chunkById(100, function ($estudiantes) use ($vacante): void {
                foreach ($estudiantes as $estudiante) {
                    $recipient = $estudiante->user;
                    if (! $recipient) {
                        continue;
                    }

                    $this->deliveries->sendOnce(
                        $recipient,
                        "vacancy.published:{$vacante->id}:user:{$recipient->id}",
                        [
                            'event' => 'vacancy.published',
                            'title' => 'Nueva oportunidad laboral',
                            'message' => $vacante->empresa->nombre_empresa.' ha publicado una vacante de '.$vacante->titulo.' relacionada con tu carrera.',
                            'vacancy_id' => $vacante->id,
                        ],
                    );
                }
            });
    }
}
