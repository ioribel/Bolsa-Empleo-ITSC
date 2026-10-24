<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Tests\TestCase;

class NotificationPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_and_read_a_database_notification(): void
    {
        $user = User::factory()->create(['rol' => 'estudiante']);
        $notification = new class extends Notification
        {
            public function via(object $notifiable): array
            {
                return ['database'];
            }

            public function toDatabase(object $notifiable): array
            {
                return [
                    'event' => 'persistence-test',
                    'title' => 'Notificación de prueba',
                    'message' => 'El almacenamiento está disponible.',
                ];
            }
        };

        $user->notify($notification);

        $storedNotification = $user->unreadNotifications()->sole();

        $this->assertSame('persistence-test', $storedNotification->data['event']);
        $this->assertSame('Notificación de prueba', $storedNotification->data['title']);
        $this->assertNull($storedNotification->read_at);
        $this->assertDatabaseHas('notifications', [
            'id' => $storedNotification->id,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
        ]);

        $storedNotification->markAsRead();

        $this->assertCount(0, $user->fresh()->unreadNotifications);
        $this->assertCount(1, $user->fresh()->readNotifications);
    }
}
