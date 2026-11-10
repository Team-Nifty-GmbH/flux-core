<?php

namespace FluxErp\Traits\Notification;

use Illuminate\Support\Str;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Default implementations for {@see \FluxErp\Contracts\RoutableToastNotification}.
 *
 * Consumers must provide `toToastNotification()` themselves; this trait
 * derives every other channel representation from it.
 */
trait DelegatesToToastNotification
{
    public function getRoute(): ?string
    {
        return null;
    }

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toBroadcast(object $notifiable): array
    {
        $data = $this->toArray($notifiable);
        $description = data_get($data, 'description');

        if (mb_strlen($description ?? '') > 500) {
            $data['description'] = Str::limit(strip_tags($description), 500);
        }

        return $data;
    }

    public function toWebPush(object $notifiable): ?WebPushMessage
    {
        if (! method_exists($notifiable, 'pushSubscriptions')
            || ! $notifiable->pushSubscriptions()->exists()
        ) {
            return null;
        }

        return $this->toToastNotification($notifiable)->toWebPush();
    }

    public function toFcm(object $notifiable): ?FcmNotification
    {
        return $this->toToastNotification($notifiable)->toFcm();
    }

    public function toFcmData(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toFcmData();
    }
}
