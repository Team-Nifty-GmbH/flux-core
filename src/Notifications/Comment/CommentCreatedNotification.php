<?php

namespace FluxErp\Notifications\Comment;

use FluxErp\Models\Comment;
use FluxErp\Models\User;
use FluxErp\Support\Notification\SubscribableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommentCreatedNotification extends SubscribableNotification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        if ($this->model->is_internal
            && ! is_a($notifiable, resolve_static(User::class, 'class'), true)
        ) {
            return [];
        }

        return parent::via($notifiable);
    }

    public function subscribe(): array
    {
        return [
            'eloquent.created: ' . resolve_static(Comment::class, 'class') => 'sendNotification',
        ];
    }

    protected function getNotificationIcon(): ?string
    {
        return 'chat';
    }

    protected function getDescription(): ?string
    {
        return $this->model->comment;
    }

    protected function getTitle(): string
    {
        return __(
            ':username commented on :model',
            [
                'username' => $this->model->getCreatedBy()?->getLabel() ?? __('Unknown'),
                'model' => __('your ' . $this->model->model_type),
            ],
        );
    }
}
