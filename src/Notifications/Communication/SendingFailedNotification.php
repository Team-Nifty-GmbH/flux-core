<?php

namespace FluxErp\Notifications\Communication;

use FluxErp\Enums\ToastType;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use FluxErp\Traits\Makeable;
use Illuminate\Bus\Queueable;
use NotificationChannels\WebPush\WebPushMessage;

class SendingFailedNotification extends Notification
{
    use Makeable, Queueable;

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->notifiable($notifiable)
            ->type(ToastType::ERROR)
            ->title(__('Emails could not be sent'))
            ->description(__('The affected communications are marked as sending failed.'));
    }

    public function toWebPush(object $notifiable): ?WebPushMessage
    {
        if (! method_exists($notifiable, 'pushSubscriptions') || ! $notifiable->pushSubscriptions()->exists()) {
            return null;
        }

        return $this->toToastNotification($notifiable)->toWebPush();
    }
}
