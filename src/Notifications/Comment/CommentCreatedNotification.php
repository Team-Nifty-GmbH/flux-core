<?php

namespace FluxErp\Notifications\Comment;

use FluxErp\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushMessage;

class CommentCreatedNotification extends Notification implements ShouldQueue
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
    public function toMail($notifiable): MailMessage
    {
        $notification = $this->toArray($notifiable);

        return (new MailMessage)
            ->subject($notification['title'])
            ->line($notification['description'])
            ->action($notification['accept']['label'] ?? '', $notification['accept']['url'] ?? '');
    }

    /**
     * Get the array representation of the notification.
     * The array should contain the folowing keys:
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
        $user = $this->model->createdBy;
        $morph = $this->model->model;

        $accept = [];
        if ($morph && method_exists($morph, 'detailRoute')) {
            $accept = [
                'accept' => [
                    'label' => __('View'),
                    'url' => $morph->setDetailRouteParams(['tab' => 'comments'])->detailRoute(),
                ],
            ];
        }

        return array_merge(
            [
                'title' => __(
                    ':username commented on your post',
                    ['username' => $user?->getLabel()],
                ),
                'description' => $this->model->comment,
                'icon' => 'chat',
                'img' => $user && method_exists($user, 'getAvatarUrl') ? $user->getAvatarUrl() : null,
            ],
            $accept
        );
    }

    public function toWebPush($notifiable): ?WebPushMessage
    {
        if (! method_exists($notifiable, 'pushSubscriptions') || ! $notifiable->pushSubscriptions()->exists()) {
            return null;
        }

        $notification = $this->toArray($notifiable);

        return (new WebPushMessage)
            ->icon($notification['img'])
            ->title($notification['title'])
            ->body($notification['description'])
            ->data(['url' => $notification['accept']['url'] ?? '']);
    }
}
