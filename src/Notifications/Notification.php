<?php

namespace FluxErp\Notifications;

use FluxErp\Models\User;
use FluxErp\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Channels\BroadcastChannel;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification as BaseNotification;

class Notification extends BaseNotification
{
    public static function defaultChannels(?object $notifiable = null): array
    {
        return is_object($notifiable)
            && method_exists($notifiable, 'getMorphClass')
            && $notifiable->getMorphClass() !== morph_alias(User::class)
                ? [
                    BroadcastChannel::class,
                    DatabaseChannel::class,
                    MailChannel::class,
                    FcmChannel::class,
                ]
                : [
                    BroadcastChannel::class,
                    DatabaseChannel::class,
                    FcmChannel::class,
                ];
    }

    public function via(object $notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return array_keys($notifiable->routes);
        }

        return method_exists($notifiable, 'notificationChannels')
            ? $notifiable->notificationChannels($this)
            : static::defaultChannels($notifiable);
    }
}
