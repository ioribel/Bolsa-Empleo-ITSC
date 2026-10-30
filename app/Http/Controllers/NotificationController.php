<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Vacante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->limit(8)->get();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Notificación',
                'message' => $notification->data['message'] ?? '',
                'created_at' => $notification->created_at->toIso8601String(),
                'created_human' => $notification->created_at->diffForHumans(),
                'unread' => $notification->read_at === null,
                'url' => route('notifications.open', $notification),
            ])->values(),
        ]);
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $item = $this->findOwnedNotification($request->user(), $notification);
        if ($item->read_at === null) {
            $item->markAsRead();
        }

        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if (! $request->expectsJson()) {
            return back()->with('status', 'Todas las notificaciones se marcaron como leídas.');
        }

        return response()->json(['unread_count' => 0]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $item = $this->findOwnedNotification($request->user(), $notification);
        $destination = $this->destination($request->user(), $item);
        if ($item->read_at === null) {
            $item->markAsRead();
        }

        return redirect()->to($destination);
    }

    private function findOwnedNotification(User $user, string $id): DatabaseNotification
    {
        return $user->notifications()->whereKey($id)->firstOrFail();
    }

    private function destination(User $user, DatabaseNotification $notification): string
    {
        $data = $notification->data;

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
