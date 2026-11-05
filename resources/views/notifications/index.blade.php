<x-app-layout>
    <x-slot name="header">
        <h1 class="font-medium text-xl text-gray-700 leading-tight">Notificaciones</h1>
    </x-slot>

    <div class="itsc-page py-6 sm:py-10">
        <section class="mx-auto max-w-4xl space-y-3 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <p role="status" aria-live="polite" class="itsc-surface p-3 text-sm text-itsc-slate-700">{{ session('status') }}</p>
            @endif
            <div class="itsc-surface flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="itsc-count text-sm">
                    {{ $notifications->total() }} {{ $notifications->total() === 1 ? 'notificación' : 'notificaciones' }}
                </p>
                @if (auth()->user()->unreadNotifications()->exists())
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-medium text-itsc-red-700 transition-colors hover:bg-itsc-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-itsc-red-600">
                            Marcar todas como leídas
                        </button>
                    </form>
                @endif
            </div>

            @forelse ($notifications as $notification)
                <a href="{{ route('notifications.open', $notification) }}"
                    class="block rounded-xl border p-4 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-itsc-red-600 {{ $notification->read_at ? 'border-itsc-slate-100 bg-white hover:bg-itsc-slate-50' : 'border-amber-200 bg-amber-50 hover:bg-amber-100' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="text-[15px] font-medium text-itsc-slate-900">{{ $notification->data['title'] ?? 'Notificación' }}</h3>
                            <p class="mt-1 text-sm leading-5 text-itsc-slate-700">{{ $notification->data['message'] ?? '' }}</p>
                        </div>
                        @unless ($notification->read_at)
                            <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-medium text-amber-800">Nueva</span>
                        @endunless
                    </div>
                    <time datetime="{{ $notification->created_at->toIso8601String() }}" class="mt-3 block text-xs text-itsc-slate-500">
                        {{ $notification->created_at->diffForHumans() }}
                    </time>
                </a>
            @empty
                <x-profile-empty-state text="Actualmente no tienes notificaciones." class="bg-white">
                    <x-slot name="icon">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4" />
                        </svg>
                    </x-slot>
                </x-profile-empty-state>
            @endforelse

            {{ $notifications->links() }}
        </section>
    </div>
</x-app-layout>
