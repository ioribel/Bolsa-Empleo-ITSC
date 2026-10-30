<?php

namespace App\Listeners;

use App\Events\JobVacancyPublished;
use App\Models\User;
use App\Models\Vacante;
use App\Services\NotificationDeliveryService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminsOfPublishedVacancy implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(private readonly NotificationDeliveryService $deliveries) {}

    public function handle(JobVacancyPublished $event): void
    {
        $vacancy = Vacante::with('empresa')
            ->where('estado', 'publicada')
            ->find($event->vacancyId);

        if (! $vacancy || ! $vacancy->empresa) {
            return;
        }

        User::query()
            ->where('rol', 'admin')
            ->chunkById(100, function ($admins) use ($vacancy): void {
                foreach ($admins as $admin) {
                    $this->deliveries->sendOnce(
                        $admin,
                        "admin.vacancy.published:{$vacancy->id}:user:{$admin->id}",
                        [
                            'event' => 'admin.vacancy.published',
                            'title' => 'Nueva vacante publicada',
                            'message' => "{$vacancy->empresa->nombre_empresa} publicó la vacante {$vacancy->titulo}.",
                            'vacancy_id' => $vacancy->id,
                        ],
                    );
                }
            });
    }
}
