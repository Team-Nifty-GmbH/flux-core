<?php

namespace FluxErp\Notifications\Pivots\CalendarEventInvites;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;

class CalendarEventInviteCreated extends Notification implements HasToastNotification, ShouldQueue
{
    use Queueable;

    public Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->toToastNotification($notifiable)->toMail();
    }

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->notifiable($notifiable)
            ->title(__(
                'Invite from :username',
                ['username' => $this->model->userCreated?->name ?? __('Unknown')])
            )
            ->icon('calendar')
            ->when($this->model->userCreated?->avatar_url, function (ToastNotification $toast) {
                return $toast->img($this->model->userCreated->avatar_url);
            })
            ->description(
                '<div class="font-semibold">' . $this->model->calendarEvent->title . '</div>' .
                '<div class="text-sm">' . $this->model->calendarEvent->starts_at . '</div>'
            )
            ->when(
                method_exists($this->model->calendarEvent, 'detailRoute'),
                function (ToastNotification $toast) {
                    return $toast->accept(NotificationAction::make()
                        ->text(__('View'))
                        ->url($this->model->calendarEvent->setDetailRouteParams()->detailRoute()
                        ));
                });
    }
}
