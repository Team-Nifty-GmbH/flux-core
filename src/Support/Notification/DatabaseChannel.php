<?php

namespace FluxErp\Support\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Channels\DatabaseChannel as BaseDatabaseChannel;
use Illuminate\Notifications\Notification;

class DatabaseChannel extends BaseDatabaseChannel
{
    public function send($notifiable, Notification $notification): Model
    {
        $payload = $this->buildPayload($notifiable, $notification);
        $payload['id'] = resolve_static(NotificationId::class, 'for', [
            'notification' => $notification,
            'notifiable' => $notifiable,
        ]);

        return $notifiable
            ->routeNotificationFor('database', $notification)
            ->updateOrCreate(
                ['id' => $payload['id']],
                $payload
            );
    }
}
