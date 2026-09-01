<?php

namespace FluxErp\Notifications\AbsenceRequest;

use FluxErp\Contracts\RoutableToastNotification;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use FluxErp\Traits\Makeable;
use FluxErp\Traits\Notification\DelegatesToToastNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AbsenceRequestSubstituteUnassignedNotification extends Notification implements RoutableToastNotification, ShouldQueue
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
            ->title(__('You are no longer substitute for :employee', [
                'employee' => $this->absenceRequest->employee?->name ?? __('Unknown'),
            ]));
    }
}
