<?php

namespace FluxErp\Notifications\Comment;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\ToastNotification\NotificationAction;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;
use NotificationChannels\WebPush\WebPushMessage;

class CommentCreatedNotification extends Notification implements HasToastNotification, ShouldQueue
{
    use Queueable;

    public Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->toToastNotification($notifiable)
            ->toMail()
            ->line(new HtmlString(
                    '<span>[flux:comment:'
                    . $this->model->model->getMorphClass() . ':'
                    . $this->model->model->getKey()
                    . ']</span>'
                )
            );
    }

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toWebPush(object $notifiable): ?WebPushMessage
    {
        if (! method_exists($notifiable, 'pushSubscriptions') || ! $notifiable->pushSubscriptions()->exists()) {
            return null;
        }

        return $this->toToastNotification($notifiable)->toWebPush();
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->notifiable($notifiable)
            ->title(__(
                ':username commented on your post',
                ['username' => $this->model->createdBy?->getLabel() ?? __('Unknown')],
            ))
            ->icon('chat')
            ->when(
                method_exists($this->model->createdBy, 'getAvatarUrl')
                && $this->model->createdBy->getAvatarUrl(),
                function (ToastNotification $toast) {
                    return $toast->img($this->model->createdBy->getAvatarUrl());
                }
            )
            ->description($this->model->comment)
            ->when(
                $this->model->model && method_exists($this->model->model, 'detailRoute'),
                function (ToastNotification $toast) {
                    return $toast->accept(
                        NotificationAction::make()
                            ->label(__('View'))
                            ->url($this->model->model->setDetailRouteParams(['tab' => 'comments'])->detailRoute())
                    );
                }
            );
    }
}
