<div class="relative" x-data="notificationBell" @keydown.escape.window="isOpen = false"
    data-feed-url="{{ route('notifications.feed') }}"
    data-read-all-url="{{ route('notifications.read-all') }}"
    data-history-url="{{ route('notifications.index') }}"
    @click.outside="isOpen = false">
    <button type="button"
        class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-100 transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-sky-400"
        aria-label="Notificaciones"
        aria-haspopup="true"
        :aria-expanded="isOpen.toString()"
        @click="isOpen = !isOpen; if (isOpen) refresh()">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4.5-5.8V4a1.5 1.5 0 0 0-3 0v1.2A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9" />
        </svg>
        <span x-cloak x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount"
            class="absolute -right-1 -top-1 min-w-5 rounded-full bg-red-500 px-1 text-center text-[10px] font-bold leading-5 text-white"></span>
    </button>

    <section x-cloak x-show="isOpen" x-transition
        class="absolute right-0 z-50 mt-2 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-lg border border-gray-200 bg-white text-left text-gray-900 shadow-xl"
        role="dialog" aria-label="Notificaciones recientes">
        <header class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold">Notificaciones</h2>
            <button type="button" class="text-xs font-medium text-indigo-700 hover:underline disabled:text-gray-400"
                :disabled="unreadCount === 0" @click="markAllRead()">
                Marcar todas como leídas
            </button>
        </header>

        <p x-cloak x-show="error" x-text="error" class="px-4 py-3 text-sm text-red-700" role="alert"></p>
        <p x-cloak x-show="isLoading && notifications.length === 0 && !error" class="px-4 py-5 text-center text-sm text-gray-500" role="status">
            Cargando notificaciones…
        </p>
        <p x-cloak x-show="!isLoading && notifications.length === 0 && !error" class="px-4 py-5 text-center text-sm text-gray-500">
            Actualmente no tienes notificaciones.
        </p>

        <ul x-show="notifications.length > 0" class="max-h-96 divide-y divide-gray-100 overflow-y-auto">
            <template x-for="notification in notifications" :key="notification.id">
                <li>
                    <a :href="notification.url"
                        class="block px-4 py-3 transition hover:bg-gray-50 focus:bg-gray-50 focus:outline-none"
                        :class="notification.unread ? 'bg-indigo-50/70' : 'bg-white'">
                        <span class="flex items-start gap-2">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                                :class="notification.unread ? 'bg-indigo-600' : 'bg-transparent'"></span>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold" x-text="notification.title"></span>
                                <span class="mt-1 block text-sm text-gray-600" x-text="notification.message"></span>
                                <time class="mt-1 block text-xs text-gray-400" :datetime="notification.created_at" x-text="notification.created_human"></time>
                            </span>
                        </span>
                    </a>
                </li>
            </template>
        </ul>

        <a href="{{ route('notifications.index') }}" class="block border-t border-gray-200 px-4 py-3 text-center text-sm font-medium text-indigo-700 hover:bg-gray-50">
            Ver todas las notificaciones
        </a>
    </section>
</div>
