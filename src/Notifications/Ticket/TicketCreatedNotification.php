<?php

namespace FluxErp\Notifications\Ticket;

use FluxErp\Models\Ticket;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushMessage;

class TicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Ticket $model;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Ticket $model)
    {
        $this->model = $model;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail(object $notifiable): MailMessage
    {
        $notification = $this->toArray($notifiable);

        return (new MailMessage())
            ->subject($notification['title'])
            ->line($notification['description'])
            ->action($notification['accept']['label'] ?? '', $notification['accept']['url'] ?? '');
    }

    public function toArray(object $notifiable): array
    {
        $user = $this->model->authenticatable;

        return ToastNotification::make()
            ->title(__(':username created ticket :id', ['username' => $user->name, 'id' => $this->model->id]))
            ->description($this->model->title . '<br>' . $this->model->description)
            ->accept(
                NotificationAction::make()
                    ->label(__('View'))
                    ->url(config('app.url') . $this->model->detailRoute(false))
            )
            ->toArray();
    }

    public function toWebPush(object $notifiable): ?WebPushMessage
    {
        if (! method_exists($notifiable, 'pushSubscriptions') || ! $notifiable->pushSubscriptions()->exists()) {
            return null;
        }

        $notification = $this->toArray($notifiable);

        return (new WebPushMessage())
            ->icon($notification['img'])
            ->title($notification['title'])
            ->body($notification['description'])
            ->data(['url' => $notification['accept']['url'] ?? '']);
    }
}
