<?php

namespace FluxErp\Notifications;

use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use FluxErp\Traits\Makeable;
use Illuminate\Bus\Queueable;
use NotificationChannels\WebPush\WebPushMessage;

class DocumentsReady extends Notification
{
    use Makeable, Queueable;

    public function __construct(protected int $count, protected ?string $filePath = null) {}

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        $toast = ToastNotification::make()
            ->notifiable($notifiable)
            ->title(__(':count Document(s) Ready', ['count' => $this->count]));

        if ($this->filePath) {
            $toast
                ->description(__('Your documents are ready for download.'))
                ->accept(
                    NotificationAction::make()
                        ->label(__('Download'))
                        ->url(route('private-storage', ['path' => $this->filePath]))
                        ->download()
                );
        } else {
            $toast->description(__('Your documents have been created.'));
        }

        return $toast;
    }

    public function toWebPush(object $notifiable): ?WebPushMessage
    {
        if (! method_exists($notifiable, 'pushSubscriptions') || ! $notifiable->pushSubscriptions()->exists()) {
            return null;
        }

        return $this->toToastNotification($notifiable)->toWebPush();
    }
}
