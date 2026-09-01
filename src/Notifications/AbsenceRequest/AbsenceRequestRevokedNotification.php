<?php

namespace FluxErp\Notifications\AbsenceRequest;

use FluxErp\Contracts\RoutableToastNotification;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use FluxErp\Traits\Makeable;
use FluxErp\Traits\Notification\DelegatesToToastNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AbsenceRequestRevokedNotification extends Notification implements RoutableToastNotification, ShouldQueue
{
    use DelegatesToToastNotification, Makeable, Queueable;

    public function __construct(protected AbsenceRequest $absenceRequest) {}

    public function getRoute(): ?string
    {
        return $this->absenceRequest->getUrl();
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->notifiable($notifiable)
            ->title(__('Your absence request was revoked'))
            ->description(__(':type — :from to :to', [
                'type' => $this->absenceRequest->absenceType?->name ?? '',
                'from' => $this->absenceRequest->start_date?->toDateString(),
                'to' => $this->absenceRequest->end_date?->toDateString(),
            ]))
            ->accept(
                NotificationAction::make()
                    ->label(__('View'))
                    ->url($this->getRoute())
            );
    }
}
