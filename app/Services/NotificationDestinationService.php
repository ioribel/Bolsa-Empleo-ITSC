<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Vacante;

class NotificationDestinationService
{
    public function for(User $user, array $data): string
    {
        if (($data['event'] ?? null) === 'admin.vacancy.published' && $user->rol === 'admin') {
            $vacancy = Vacante::query()
                ->where('estado', 'publicada')
                ->find($data['vacancy_id'] ?? null);

            if ($vacancy) {
                return route('admin.vacantes.index', ['buscar' => $vacancy->titulo]);
            }
        }

        if (($data['event'] ?? null) === 'admin.company.pending' && $user->rol === 'admin') {
            $company = Empresa::query()
                ->where('estado', 'pendiente')
                ->find($data['company_id'] ?? null);

            if ($company) {
                return route('admin.empresas.index', [
                    'estado' => 'pendiente',
                    'buscar' => $company->nombre_empresa,
                ]);
            }

            return route('admin.empresas.index', ['estado' => 'pendiente']);
        }

        if (($data['event'] ?? null) === 'vacancy.published' && $user->estudiante) {
            $vacancy = Vacante::query()
                ->where('estado', 'publicada')
                ->whereHas('carreras', fn ($query) => $query->where('carreras.id', $user->estudiante->carrera_id))
                ->find($data['vacancy_id'] ?? null);

            if ($vacancy) {
                return route('estudiante.vacantes.show', $vacancy);
            }
        }

        if (($data['event'] ?? null) === 'application.received' && $user->empresa) {
            $vacancy = $user->empresa->vacantes()->find($data['vacancy_id'] ?? null);
            $application = $vacancy?->postulaciones()->find($data['application_id'] ?? null);

            if ($vacancy && $application) {
                return route('empresa.vacantes.postulaciones.index', $vacancy);
            }
        }

        if (in_array($data['event'] ?? null, ['application.aceptada', 'application.rechazada'], true)
            && $user->estudiante) {
            $application = $user->estudiante->postulaciones()->find($data['application_id'] ?? null);

            if ($application) {
                return route('estudiante.postulaciones.index', ['estado' => $application->estado]);
            }
        }

        return route('notifications.index');
    }
}
