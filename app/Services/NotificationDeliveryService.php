<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PlatformDatabaseNotification;
use Illuminate\Support\Facades\DB;

class NotificationDeliveryService
{
    public function sendOnce(User $recipient, string $eventKey, array $data): void
    {
        DB::transaction(function () use ($recipient, $eventKey, $data): void {
            $inserted = DB::table('notification_deliveries')->insertOrIgnore([
                'event_key' => $eventKey,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($inserted === 1) {
                $recipient->notify(new PlatformDatabaseNotification($data));
            }
        });
    }
}
