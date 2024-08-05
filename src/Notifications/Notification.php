<?php

namespace FluxErp\Notifications;

use FluxErp\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Channels\BroadcastChannel;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification as BaseNotification;

class Notification extends BaseNotification
{
    public function via(object $notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return array_keys($notifiable->routes);
        }

        return method_exists($notifiable, 'notificationChannels')
            ? $notifiable->notificationChannels($this)
            : static::defaultChannels($notifiable);
    }

    public static function defaultChannels(?object $notifiable = null): array
    {
        return is_object($notifiable)
            && method_exists($notifiable, 'getMorphClass')
            && $notifiable->getMorphClass() !== morph_alias(User::class)
                ? [
                    BroadcastChannel::class,
                    DatabaseChannel::class,
                    MailChannel::class,
                ]
                : [
                    BroadcastChannel::class,
                    DatabaseChannel::class,
                ];
    }
}
