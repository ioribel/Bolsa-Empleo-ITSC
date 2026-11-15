<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\NotificationDestinationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationDestinationService $destinations) {}

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
        $destination = $this->destinations->for($request->user(), $item->data);
        if ($item->read_at === null) {
            $item->markAsRead();
        }

        return redirect()->to($destination);
    }

    private function findOwnedNotification(User $user, string $id): DatabaseNotification
    {
        return $user->notifications()->whereKey($id)->firstOrFail();
    }
}
