<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-700 leading-tight">Notificaciones</h2>
    </x-slot>

    <div class="py-8">
        <main class="mx-auto max-w-4xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <p class="rounded-md bg-green-100 p-3 text-sm text-green-800">{{ session('status') }}</p>
            @endif
            <div class="flex items-center justify-between gap-4">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ $notifications->total() }} {{ $notifications->total() === 1 ? 'notificación' : 'notificaciones' }}
                </p>
                @if (auth()->user()->unreadNotifications()->exists())
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-sm font-medium text-indigo-700 underline dark:text-indigo-300">
                            Marcar todas como leídas
                        </button>
                    </form>
                @endif
            </div>

            @forelse ($notifications as $notification)
                <a href="{{ route('notifications.open', $notification) }}"
                    class="block rounded-lg border p-4 shadow-sm transition hover:shadow-md dark:bg-gray-800 {{ $notification->read_at ? 'border-gray-200 bg-white dark:border-gray-700' : 'border-indigo-200 bg-indigo-50 dark:border-indigo-900 dark:bg-indigo-950/30' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $notification->data['title'] ?? 'Notificación' }}</h3>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $notification->data['message'] ?? '' }}</p>
                        </div>
                        @unless ($notification->read_at)
                            <span class="shrink-0 rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-800">Nueva</span>
                        @endunless
                    </div>
                    <time datetime="{{ $notification->created_at->toIso8601String() }}" class="mt-3 block text-xs text-gray-500 dark:text-gray-400">
                        {{ $notification->created_at->diffForHumans() }}
                    </time>
                </a>
            @empty
                <p class="rounded-lg bg-white p-6 text-center text-sm text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-400">
                    Actualmente no tienes notificaciones.
                </p>
            @endforelse

            {{ $notifications->links() }}
        </main>
    </div>
</x-app-layout>
