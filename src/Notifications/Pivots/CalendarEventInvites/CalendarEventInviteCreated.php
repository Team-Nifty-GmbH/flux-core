<?php

namespace FluxErp\Notifications\Pivots\CalendarEventInvites;

use FluxErp\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;

class CalendarEventInviteCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public Model $model;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Model $model)
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

        return (new MailMessage)
            ->subject($notification['title'])
            ->line($notification['description'])
            ->action($notification['accept']['label'] ?? '', $notification['accept']['url'] ?? '');
    }

    public function toArray(object $notifiable): array
    {
        $user = $this->model->userCreated ?? null;
        $morph = $this->model->calendarEvent;

        $accept = [];
        if (method_exists($morph, 'detailRoute')) {
            $accept = [
                'accept' => [
                    'label' => __('View'),
                    'url' => $morph->setDetailRouteParams()->detailRoute(),
                ],
            ];
        }

        return array_merge(
            [
                'title' => __(
                    'Invite from :username',
                    ['username' => $user?->name],
                ),
                'timeout' => false,
                'description' => '<div class="font-semibold">' . $morph->title . '</div>' .
                    '<div class="text-sm">' . $morph->starts_at . '</div>',
                'icon' => 'calendar',
                'img' => $user?->avatar_url,
            ],
            $accept
        );
    }
}
