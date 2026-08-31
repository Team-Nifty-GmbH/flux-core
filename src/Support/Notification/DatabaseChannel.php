<?php

namespace FluxErp\Support\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Channels\DatabaseChannel as BaseDatabaseChannel;
use Illuminate\Notifications\Notification;

class DatabaseChannel extends BaseDatabaseChannel
{
    public function send($notifiable, Notification $notification): Model
    {
        return $notifiable->routeNotificationFor('database', $notification)->updateOrCreate(
            ['id' => $notification->id],
            $this->buildPayload($notifiable, $notification)
        );
    }
}
