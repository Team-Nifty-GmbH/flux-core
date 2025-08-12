<?php

namespace FluxErp\Notifications\Task;

use FluxErp\Models\Task;
use FluxErp\Support\Notification\SubscribableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskCreatedNotification extends SubscribableNotification implements ShouldQueue
{
    use Queueable;

    public function subscribe(): array
    {
        return [
            'eloquent.created: ' . morph_alias(Task::class) => 'sendNotification',
        ];
    }

    protected function getDescription(): ?string
    {
        return $this->model->getLabel();
    }

    protected function getNotificationIcon(): ?string
    {
        return 'clipboard-list';
    }

    protected function getTitle(): string
    {
        return __(
            ':username created a task',
            [
                'username' => $this->model->getCreatedBy()?->getLabel() ?? __('Unknown'),
            ],
        );
    }
}
