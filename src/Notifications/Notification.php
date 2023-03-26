<?php

namespace FluxErp\Notifications;

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Channels\BroadcastChannel;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Notification as BaseNotification;

class Notification extends BaseNotification
{
    public function via($notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return array_keys($notifiable->routes);
        }

        return method_exists($notifiable, 'notificationChannels')
            ? $notifiable->notificationChannels($this)
            : $this->defaultChannels();
    }

    public static function defaultChannels(): array
    {
        return [
            BroadcastChannel::class,
            DatabaseChannel::class,
        ];
    }
}
