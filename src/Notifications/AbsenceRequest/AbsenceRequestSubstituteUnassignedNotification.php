<?php

namespace FluxErp\Notifications\AbsenceRequest;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use FluxErp\Traits\Makeable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\WebPush\WebPushMessage;

class AbsenceRequestSubstituteUnassignedNotification extends Notification implements HasToastNotification, ShouldQueue
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
            ->title(__('You are no longer substitute for :employee', [
                'employee' => $this->absenceRequest->employee?->name ?? __('Unknown'),
            ]));
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
