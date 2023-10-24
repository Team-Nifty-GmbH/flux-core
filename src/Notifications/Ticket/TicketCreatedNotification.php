<?php

namespace FluxErp\Notifications\Ticket;

use FluxErp\Models\Ticket;
use FluxErp\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

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
    public function toMail($notifiable): MailMessage
    {
        $notification = $this->toArray($notifiable);

        return (new MailMessage)
            ->subject($notification['title'])
            ->greeting($notification['subtitle'])
            ->line($notification['description'])
            ->action($notification['accept']['label'] ?? '', $notification['accept']['url'] ?? '');
    }

    /**
     * Get the array representation of the notification.
     * The array should contain the following keys:
     * - title (string)
     * - description (string)
     * - icon (string, all heroicons)
     * - img (string, url to image)
     * - accept (array, contains the following keys)
     *     - label (string, required)
     *     - url (string, required)
     * - reject (array, contains the following keys)
     *     - label (string, required)
     *     - url (string, required)
     *
     * @param  mixed  $notifiable
     */
    public function toArray($notifiable): array
    {
        $user = $this->model->authenticatable;

        return [
            'title' => __(
                ':username created ticket :id',
                [
                    'username' => $user->name,
                    'id' => $this->model->id,
                ],
            ),
            'subtitle' => $this->model->title,
            'description' => $this->model->description,
            'icon' => 'info',
            'img' => method_exists($user, 'getAvatarUrl') ? $user->getAvatarUrl() : null,
            'accept' => [
                'label' => __('View'),
                'url' => config('app.url') . $this->model->detailRoute(false),
            ],
        ];
    }
}
