import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('notificationBell', () => ({
    isOpen: false,
    isLoading: false,
    error: '',
    unreadCount: 0,
    notifications: [],
    timer: null,

    init() {
        this.refresh();
        this.timer = window.setInterval(() => {
            if (document.visibilityState === 'visible') {
                this.refresh();
            }
        }, 30000);
    },

    async refresh() {
        if (this.isLoading) {
            return;
        }

        this.isLoading = true;
        this.error = '';

        try {
            const response = await fetch(this.$el.dataset.feedUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                throw new Error('No se pudieron cargar las notificaciones.');
            }

            const data = await response.json();
            this.unreadCount = data.unread_count;
            this.notifications = data.notifications;
        } catch (error) {
            this.error = error.message;
        } finally {
            this.isLoading = false;
        }
    },

    async markAllRead() {
        this.error = '';

        try {
            const response = await fetch(this.$el.dataset.readAllUrl, {
                method: 'PATCH',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                throw new Error('No se pudieron marcar como leídas.');
            }

            await this.refresh();
        } catch (error) {
            this.error = error.message;
        }
    },
}));

Alpine.start();
