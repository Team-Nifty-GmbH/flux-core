<?php

namespace FluxErp\Notifications\Communication;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Enums\ToastType;
use FluxErp\Models\Communication;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use FluxErp\Traits\Makeable;
use Illuminate\Bus\Queueable;

class SendingFailedNotification extends Notification implements HasToastNotification
{
    use Makeable, Queueable;

    public function __construct(public Communication $communication) {}

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->notifiable($notifiable)
            ->type(ToastType::ERROR)
            ->persistent()
            ->title(__('Email could not be sent'))
            ->description(
                e(
                    implode(
                        ': ',
                        array_filter([$this->communication->subject, $this->communication->send_error])
                    )
                )
            );
    }
}
