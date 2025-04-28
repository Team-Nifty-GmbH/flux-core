<?php

namespace FluxErp\Notifications;

use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use FluxErp\Traits\Makeable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\WebPushMessage;

class ExportReady extends Notification
{
    use Makeable, Queueable;

    public function __construct(protected string $filePath, protected string $model) {}

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->notifiable($notifiable)
            ->title(__(':model Export Ready', ['model' => __(Str::headline($this->model))]))
            ->description(__('Your export is ready for download.'))
            ->accept(
                NotificationAction::make()
                    ->label(__('Download'))
                    ->url(Storage::url($this->filePath))
                    ->download()
            );
    }

    public function toWebPush(object $notifiable): ?WebPushMessage
    {
        if (! method_exists($notifiable, 'pushSubscriptions') || ! $notifiable->pushSubscriptions()->exists()) {
            return null;
        }

        return $this->toToastNotification($notifiable)->toWebPush();
    }
}
