<?php

namespace FluxErp\Notifications\AbsenceRequest;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use FluxErp\Traits\Makeable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\WebPush\WebPushMessage;

class AbsenceRequestRejectedNotification extends Notification implements HasToastNotification, ShouldQueue
{
    use Makeable, Queueable;

    public function __construct(protected AbsenceRequest $absenceRequest) {}

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->notifiable($notifiable)
            ->title(__('Your absence request was rejected'))
            ->description(__(':type — :from to :to', [
                'type' => $this->absenceRequest->absenceType?->name ?? '',
                'from' => $this->absenceRequest->start_date?->toDateString(),
                'to' => $this->absenceRequest->end_date?->toDateString(),
            ]))
            ->accept(
                NotificationAction::make()
                    ->label(__('View'))
                    ->url($this->absenceRequest->getUrl())
            );
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
}
